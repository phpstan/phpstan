import {LSPClient, serverCompletion, signatureHelp} from '@codemirror/lsp-client';
import {createPhpantomTransport} from './transport';

// Stable virtual URI for the single playground document.
export const PHP_URI = 'file:///try/main.php';

// One PHPantom LSP client per page, talking to the wasm worker. We include
// only completion + signature help here: hover is rendered by our own
// Prism-highlighted source (editor/phpantomHover.ts) via phpantomLsp.request(),
// and diagnostics stay with PHPStan on the server (its result panel is
// authoritative — we don't want PHPantom squiggles disagreeing with it).
const client = new LSPClient({
	// The worker answers `initialize` only after downloading and compiling the
	// ~11 MB wasm, which can take minutes on slow connections, and it handles
	// messages one at a time, so a timeout never frees anything up. A timed-out
	// request instead rejects promises lsp-client leaves unhandled (its
	// notifications after a failed `initialize`, trigger-character signature
	// help). The worker answers every request itself, even when it fails.
	timeout: 2 ** 31 - 1,
	extensions: [serverCompletion(), signatureHelp()],
});

// Even the longest timeout fires when a headless renderer fast-forwards timers
// or a tab stays open for weeks, so lsp-client's private timeout handler is
// replaced to never reject a request.
Object.assign(client, {timeoutRequest: () => undefined});

export const phpantomLsp = client.connect(createPhpantomTransport());
