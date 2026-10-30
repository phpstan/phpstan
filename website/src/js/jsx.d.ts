// Types for TSX templates rendered by TKO's ko.jsx (configured as the JSX
// factory in tsconfig.json and vite.config.js). Every element accepts the HTML
// and SVG attributes listed below and the `ko-*` bindings from KoBindings, so
// tsc rejects unknown attributes, unknown bindings and wrongly typed values.
import type ko from '@tko/build.reference';

type AttributeValue = string | number | undefined;
type ReactiveAttribute = AttributeValue | ko.Subscribable<AttributeValue>;

type Child = JSX.Element | string | number | null | undefined | Node | ko.Subscribable<JSX.Element | JSX.Element[] | string | null> | Child[];

type EventHandlers = {
	[K in keyof HTMLElementEventMap]?: (data: unknown, event: HTMLElementEventMap[K]) => unknown;
};

declare global {
	/**
	 * The built-in bindings, written as `ko-name={value}`. Event handlers
	 * prevent the default action unless they return true. Custom binding
	 * handlers declare their bindings by augmenting KoCustomBindings.
	 */
	interface KoBindings extends KoCustomBindings {
		'ko-attr'?: Record<string, ko.MaybeSubscribable<string | number | null | undefined>>;
		'ko-checked'?: ko.Observable<any>;
		'ko-class'?: ko.MaybeSubscribable<string>;
		'ko-click'?: (data: unknown, event: MouseEvent) => unknown;
		'ko-component'?: {name: string; params?: unknown};
		'ko-css'?: Record<string, ko.MaybeSubscribable<boolean>>;
		'ko-disable'?: ko.MaybeSubscribable<boolean>;
		'ko-enable'?: ko.MaybeSubscribable<boolean>;
		'ko-event'?: EventHandlers;
		'ko-html'?: ko.MaybeSubscribable<string | null>;
		'ko-options'?: ko.MaybeSubscribable<readonly unknown[]>;
		'ko-optionsText'?: string | ((item: any) => string);
		'ko-optionsValue'?: string;
		'ko-style'?: Record<string, ko.MaybeSubscribable<string | number | null>>;
		'ko-submit'?: (form: HTMLFormElement) => unknown;
		'ko-text'?: ko.MaybeSubscribable<string | number | null>;
		'ko-textInput'?: ko.Observable<string>;
		'ko-value'?: ko.Observable<any>;
		'ko-valueUpdate'?: 'input' | 'keyup' | 'keypress' | 'afterkeydown';
		'ko-visible'?: ko.MaybeSubscribable<boolean>;
	}

	// eslint-disable-next-line @typescript-eslint/no-empty-object-type -- augmented by custom binding handlers
	interface KoCustomBindings {}

	/** KoBindings without the `ko-` prefix, for ko.applyBindingsToNode(). */
	type KoNodeBindings = {
		[K in keyof KoBindings as K extends `ko-${infer Name}` ? Name : never]: KoBindings[K];
	};

	interface HtmlAttributes extends KoBindings {
		children?: Child;
		[data: `data-${string}`]: ReactiveAttribute;
		[aria: `aria-${string}`]: ReactiveAttribute;
		alt?: ReactiveAttribute;
		autocomplete?: ReactiveAttribute;
		class?: ReactiveAttribute;
		for?: ReactiveAttribute;
		href?: ReactiveAttribute;
		id?: ReactiveAttribute;
		name?: ReactiveAttribute;
		placeholder?: ReactiveAttribute;
		rel?: ReactiveAttribute;
		role?: ReactiveAttribute;
		rows?: ReactiveAttribute;
		src?: ReactiveAttribute;
		style?: ReactiveAttribute;
		target?: ReactiveAttribute;
		title?: ReactiveAttribute;
		type?: ReactiveAttribute;
		value?: ReactiveAttribute;

		// SVG
		attributeName?: ReactiveAttribute;
		begin?: ReactiveAttribute;
		calcMode?: ReactiveAttribute;
		'clip-rule'?: ReactiveAttribute;
		cx?: ReactiveAttribute;
		cy?: ReactiveAttribute;
		d?: ReactiveAttribute;
		dur?: ReactiveAttribute;
		fill?: ReactiveAttribute;
		'fill-opacity'?: ReactiveAttribute;
		'fill-rule'?: ReactiveAttribute;
		from?: ReactiveAttribute;
		height?: ReactiveAttribute;
		r?: ReactiveAttribute;
		repeatCount?: ReactiveAttribute;
		stroke?: ReactiveAttribute;
		'stroke-linecap'?: ReactiveAttribute;
		'stroke-linejoin'?: ReactiveAttribute;
		'stroke-width'?: ReactiveAttribute;
		to?: ReactiveAttribute;
		values?: ReactiveAttribute;
		viewBox?: ReactiveAttribute;
		width?: ReactiveAttribute;
		xmlns?: ReactiveAttribute;
	}

	namespace JSX {
		/** What ko.jsx.createElement() returns; ko.jsx.render() turns it into DOM nodes. */
		interface Element {
			elementName: string;
			attributes: HtmlAttributes;
			children: Child[];
		}

		// ko.jsx renders only DOM elements; components are plain functions
		// called as {View(viewModel)}, not used as <View/> tags.
		type ElementType = keyof IntrinsicElements;

		type IntrinsicElements = Record<keyof HTMLElementTagNameMap | keyof SVGElementTagNameMap, HtmlAttributes>;

		interface ElementChildrenAttribute {
			children: unknown;
		}
	}
}
