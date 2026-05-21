// Web Worker hosting the PHPantom language server (compiled to wasm32-wasip1)
// under a browser WASI runtime. It speaks LSP JSON-RPC: each posted message is
// marshalled into wasm memory, handed to `lsp_handle`, and the response posted
// back. Notifications produce no response.
//
// We target WASI rather than wasm-bindgen because PHPantom's completion path
// hits a memory-corruption bug on wasm32-unknown-unknown but is correct on
// wasm32-wasip1.
import {WASI, OpenFile, File as WasiFile, ConsoleStdout} from '@bjorn3/browser_wasi_shim';
// PHPStan stub shells fetched from phpstan-src by `npm run build:stubs`. Opened
// as hidden documents (below) so the editor autocompletes PHPStan's own symbols
// alongside phpstorm-stubs and the file being edited.
import testingStub from './stubs/Testing.php?raw';

const STUBS: ReadonlyArray<{uri: string; text: string}> = [
	{uri: 'file:///stubs/phpstan/Testing.php', text: testingStub},
];

// `self` is a DedicatedWorkerGlobalScope here; typed loosely to avoid pulling
// the webworker lib into the project's DOM-typed build.
const ctx = self as unknown as {
	onmessage: ((e: MessageEvent) => void) | null;
	postMessage: (message: unknown) => void;
};

interface LspExports {
	memory: WebAssembly.Memory;
	lsp_alloc: (len: number) => number;
	lsp_dealloc: (ptr: number, len: number) => void;
	// Returns a pointer to the response (length via lsp_response_len), or 0.
	lsp_handle: (ptr: number, len: number) => number;
	lsp_response_len: () => number;
}

let lsp: LspExports | null = null;
let initPromise: Promise<void> | null = null;

function ensureReady(): Promise<void> {
	if (initPromise === null) {
		initPromise = (async () => {
			const url = new URL('./pkg-wasi/phpantom_lsp.wasm', import.meta.url);
			const wasi = new WASI([], [], [
				new OpenFile(new WasiFile([])), // fd 0: stdin (unused)
				ConsoleStdout.lineBuffered((m: string) => console.log('[phpantom]', m)), // fd 1
				ConsoleStdout.lineBuffered((m: string) => console.warn('[phpantom]', m)), // fd 2
			]);
			const bytes = await (await fetch(url)).arrayBuffer();
			const module = await WebAssembly.compile(bytes);
			const instance = await WebAssembly.instantiate(module, {
				wasi_snapshot_preview1: wasi.wasiImport,
			});
			wasi.initialize(instance as unknown as Parameters<WASI['initialize']>[0]); // reactor
			lsp = instance.exports as unknown as LspExports;
		})();
	}
	return initPromise;
}

function callHandle(message: string): string | undefined {
	const ex = lsp!;
	const bytes = new TextEncoder().encode(message);
	const inPtr = ex.lsp_alloc(bytes.length);
	new Uint8Array(ex.memory.buffer, inPtr, bytes.length).set(bytes);
	const outPtr = ex.lsp_handle(inPtr, bytes.length);
	ex.lsp_dealloc(inPtr, bytes.length);
	if (outPtr === 0) {
		return undefined;
	}
	const outLen = ex.lsp_response_len();
	// .slice() copies out of wasm memory before we free it (and before any
	// later call grows/detaches the buffer).
	const response = new TextDecoder().decode(new Uint8Array(ex.memory.buffer, outPtr, outLen).slice());
	ex.lsp_dealloc(outPtr, outLen);
	return response;
}

let stubsLoaded = false;

// Open the PHPStan stub shells once, right after the client's `initialize`, so
// their symbols are indexed before any completion request. They use hidden
// `file:///stubs/...` URIs the editor never references.
function loadStubs(): void {
	for (const stub of STUBS) {
		callHandle(JSON.stringify({
			jsonrpc: '2.0',
			method: 'textDocument/didOpen',
			params: {textDocument: {uri: stub.uri, languageId: 'php', version: 1, text: stub.text}},
		}));
	}
}

ctx.onmessage = async (e: MessageEvent) => {
	const message = e.data as string;
	await ensureReady();
	const response = callHandle(message);
	if (!stubsLoaded) {
		stubsLoaded = true;
		loadStubs();
	}
	if (response !== undefined) {
		ctx.postMessage(response);
	}
};
