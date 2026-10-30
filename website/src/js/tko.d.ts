// TKO (Technical Knockout) 4 ships no TypeScript declarations yet
// (https://github.com/knockout/tko/issues/384), so these describe the part of
// @tko/build.reference this website uses. Observable typings follow the ones
// Knockout 3 shipped; JSX typings for TSX templates are in jsx.d.ts.
declare module '@tko/build.reference' {
	namespace ko {
		type MaybeSubscribable<T = any> = T | Subscribable<T>;

		interface Subscription {
			dispose(): void;
		}

		interface Subscribable<T = any> {
			(): T;
			subscribe(callback: (value: T) => void): Subscription;
			extend(extenders: {
				notify?: 'always';
				rateLimit?: number | {timeout: number; method?: 'notifyAtFixedRate' | 'notifyWhenChangesStop'};
			}): this;
			peek(): T;
		}

		interface Observable<T = any> extends Subscribable<T> {
			(value: T): void;
		}

		interface ObservableArray<T = any> extends Observable<T[]> {
			push(...items: T[]): number;
			splice(start: number, deleteCount?: number, ...items: T[]): T[];
			remove(item: T): T[];
		}

		interface Computed<T = any> extends Subscribable<T> {
			dispose(): void;
		}

		type PureComputed<T = any> = Computed<T>;

		function observable<T>(value: T): Observable<T>;
		function observable<T = any>(value: null): Observable<T | null>;
		function observable<T = any>(): Observable<T | undefined>;

		function observableArray<T = any>(initialValue?: T[]): ObservableArray<T>;

		function computed<T>(evaluator: () => T): Computed<T>;
		function pureComputed<T>(evaluator: () => T): PureComputed<T>;

		function unwrap<T>(value: MaybeSubscribable<T>): T;

		interface AllBindings {
			get(name: string): any;
			has(name: string): boolean;
		}

		interface BindingHandler {
			init?(element: HTMLElement, valueAccessor: () => any, allBindings: AllBindings): void | {controlsDescendantBindings: boolean};
			update?(element: HTMLElement, valueAccessor: () => any, allBindings: AllBindings): void;
		}

		const bindingHandlers: Record<string, BindingHandler>;

		/** Binds the TSX-rendered `ko-*` attributes under (and on) rootNode. */
		function applyBindings(viewModel: object, rootNode: Element): Promise<void>;

		/** Applies the given bindings to node only, leaving its descendants alone. */
		function applyBindingsToNode(node: Node, bindings: KoNodeBindings, viewModel?: object): void;

		const options: {
			deferUpdates: boolean;
		};

		const utils: {
			domData: {
				get(node: Node, key: string): any;
				set(node: Node, key: string, value: unknown): void;
			};
			setTextContent(element: Node, textContent: MaybeSubscribable<string>): void;
		};

		const jsx: {
			createElement(elementName: string | symbol, attributes: object | null, ...children: unknown[]): JSX.Element;
			Fragment: symbol;
			/** Converts JSX to DOM nodes; bind them with applyBindings once they are in the document. */
			render(jsx: JSX.Element): {node: Node; dispose(): void};
		};
	}

	export default ko;
}
