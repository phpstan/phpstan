import {Transport} from '@codemirror/lsp-client';
import {isPhpantomReady} from './ready';

// A @codemirror/lsp-client Transport that runs the PHPantom language server
// (compiled to wasm) inside a Web Worker, exchanging LSP JSON-RPC messages.
// Resolves once the worker has loaded the wasm module.
export function createPhpantomTransport(): Promise<Transport> {
	const worker = new Worker(new URL('./worker.ts', import.meta.url), {type: 'module'});
	const handlers = new Set<(value: string) => void>();

	return new Promise((resolve) => {
		worker.onmessage = (e: MessageEvent) => {
			if (isPhpantomReady(e.data)) {
				resolve({
					send(message: string) {
						worker.postMessage(message);
					},
					subscribe(handler: (value: string) => void) {
						handlers.add(handler);
					},
					unsubscribe(handler: (value: string) => void) {
						handlers.delete(handler);
					},
				});
				return;
			}
			const message = e.data as string;
			for (const handler of handlers) {
				handler(message);
			}
		};
	});
}
