// Posted by the worker once the wasm module is instantiated. Not a string, so
// it can't be confused with an LSP JSON-RPC message.
export const PHPANTOM_READY = {phpantomReady: true} as const;

export function isPhpantomReady(data: unknown): boolean {
	return typeof data === 'object' && data !== null && (data as {phpantomReady?: unknown}).phpantomReady === true;
}
