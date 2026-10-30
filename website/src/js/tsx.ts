import ko from '@tko/build.reference';

/**
 * Renders a TSX template in place of the element with the given id and binds
 * its `ko-*` attributes. Bindings are only ever applied to TSX-rendered nodes,
 * never to the server-rendered page around them: @tko/build.reference would
 * turn any `{{ … }}` in its text, like Twig in code samples, into bindings.
 */
export function mount(id: string, template: JSX.Element): void {
	const placeholder = document.getElementById(id);
	if (placeholder === null) {
		return;
	}

	const {node} = ko.jsx.render(template);
	if (!(node instanceof Element)) {
		throw new Error(`The template for #${id} must have a single root element.`);
	}

	placeholder.replaceWith(node);
	void ko.applyBindings({}, node);
}

/**
 * Applies bindings to a server-rendered element that wraps static content,
 * without binding (or re-rendering) anything inside it.
 */
export function bindElement(id: string, bindings: KoNodeBindings): void {
	const element = document.getElementById(id);
	if (element !== null) {
		ko.applyBindingsToNode(element, bindings);
	}
}

/**
 * Renders the template while the condition holds. The template is rendered
 * again only when the condition flips, or when an observable it reads changes.
 */
export function when(condition: () => boolean, template: () => JSX.Element): ko.PureComputed<JSX.Element | null> {
	const holds = ko.pureComputed(condition);
	return ko.pureComputed(() => holds() ? template() : null);
}
