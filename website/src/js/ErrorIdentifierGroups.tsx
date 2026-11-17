import ko from '@tko/build.reference';
import {bindElement, mount} from './tsx';

// The "All / Auto-fixable" filter above the server-rendered list of error identifier groups on /error-identifiers.
export function initErrorIdentifierGroups(): void {
	const autofixableOnly = ko.observable(false);
	const all = ko.pureComputed(() => !autofixableOnly());
	const buttonClass = 'px-3 py-1.5 text-sm font-medium rounded-md transition duration-150 cursor-pointer';
	const activeClass = 'bg-white text-gray-900 shadow-sm';
	const inactiveClass = 'text-gray-500 hover:text-gray-800';

	mount('identifier-groups-filter', (
		<div class="inline-flex p-1 bg-gray-100 rounded-lg" role="group" aria-label="Filter error identifiers">
			<button type="button" class={buttonClass} ko-css={{[activeClass]: all, [inactiveClass]: autofixableOnly}} ko-attr={{'aria-pressed': ko.pureComputed(() => String(all()))}} ko-click={() => autofixableOnly(false)}>All groups</button>
			<button type="button" class={buttonClass} ko-css={{[activeClass]: autofixableOnly, [inactiveClass]: all}} ko-attr={{'aria-pressed': ko.pureComputed(() => String(autofixableOnly()))}} ko-click={() => autofixableOnly(true)}>🔧 Auto-fixable only</button>
		</div>
	));

	bindElement('identifier-groups', {css: {'autofixable-only': autofixableOnly}});
}
