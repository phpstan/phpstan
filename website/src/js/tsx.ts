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
 * Renders a component in place of the element with the given id, registering
 * it under its customElementName first if needed.
 * The component is bound between two comments rather than inside an element,
 * so the page gets the template's markup and nothing around it. If the
 * component's constructor throws, the element stays and the error propagates.
 */
export function mountComponent<P extends unknown[]>(
	id: string,
	component: (new (...params: P) => ko.Component) & Pick<typeof ko.Component, 'customElementName' | 'register'>,
	...params: P
): void {
	const placeholder = document.getElementById(id);
	if (placeholder === null) {
		return;
	}

	if (!ko.components.isRegistered(component.customElementName)) {
		component.register();
	}

	const start = document.createComment('ko component');
	const end = document.createComment('/ko');
	placeholder.before(start, end);
	try {
		ko.applyBindingsToNode(start, {component: {name: component.customElementName, params: params[0]}});
	} catch (e) {
		start.remove();
		end.remove();
		throw e;
	}

	placeholder.remove();
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
