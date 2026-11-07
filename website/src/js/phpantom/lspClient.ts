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
	extensions: [serverCompletion(), signatureHelp()],
});

// lsp-client has no option to disable its request timeout. The worker answers
// `initialize` only after downloading and compiling the ~17 MB wasm, and it
// handles messages one at a time, so a timeout never frees anything up. A
// timed-out request instead rejects promises lsp-client leaves unhandled (its
// notifications after a failed `initialize`, trigger-character signature help).
// Even a huge timeout fires early where timers are fast-forwarded, such as
// crawlers rendering shared playground links. The worker answers every request
// itself, even when it fails, so requests are never timed out.
(client as unknown as {timeoutRequest: () => void}).timeoutRequest = () => undefined;

export const phpantomLsp = client.connect(createPhpantomTransport());
