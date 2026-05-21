import {Transport} from '@codemirror/lsp-client';

// A @codemirror/lsp-client Transport that runs the PHPantom language server
// (compiled to wasm) inside a Web Worker, exchanging LSP JSON-RPC messages.
export function createPhpantomTransport(): Transport {
	const worker = new Worker(new URL('./worker.ts', import.meta.url), {type: 'module'});
	const handlers = new Set<(value: string) => void>();

	worker.onmessage = (e: MessageEvent) => {
		const message = e.data as string;
		for (const handler of handlers) {
			handler(message);
		}
	};

	return {
		send(message: string) {
			worker.postMessage(message);
		},
		subscribe(handler: (value: string) => void) {
			handlers.add(handler);
		},
		unsubscribe(handler: (value: string) => void) {
			handlers.delete(handler);
		},
	};
}
