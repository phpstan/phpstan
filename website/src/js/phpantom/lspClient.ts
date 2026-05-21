import {LSPClient, serverCompletion, signatureHelp} from '@codemirror/lsp-client';
import {createPhpantomTransport} from './transport';

// Stable virtual URI for the single playground document.
export const PHP_URI = 'file:///try/main.php';

// One PHPantom LSP client per page, talking to the wasm worker. We include
// only completion + signature help here: hover is rendered by our own
// Prism-highlighted source (editor/phpantomHover.ts) via phpantomLsp.request(),
// and diagnostics stay with PHPStan on the server (its result panel is
// authoritative — we don't want PHPantom squiggles disagreeing with it).
export const phpantomLsp = new LSPClient({
	extensions: [serverCompletion(), signatureHelp()],
}).connect(createPhpantomTransport());
