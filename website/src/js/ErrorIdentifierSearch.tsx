import ko from '@tko/build.reference';
import {ErrorIdentifierSearchViewModel} from './ErrorIdentifierSearchViewModel';
import {mount, when} from './tsx';

// The search box and random examples shown on /documentation and /error-identifiers.
export function initErrorIdentifierSearch(examplesTarget?: string): void {
	const search = new ErrorIdentifierSearchViewModel();

	mount('identifier-search-widget', (
		<div>
			<div class="relative max-w-md">
				<input id="identifier-search" type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-sm font-mono focus:border-blue-500 focus:outline-none transition duration-150" placeholder="e.g. argument.type, return.missing, deadCode..." ko-textInput={search.query} ko-event={{keydown: search.onKeyDown, focus: search.onFocus, blur: search.onBlur}} autocomplete="off" />
				{when(search.showDropdown, () => (
					<div class="absolute z-30 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
						{ko.pureComputed(() => search.results().map((result, index) => (
							<a class={`block px-4 py-2.5 font-mono text-sm no-underline transition duration-100 ${index === search.selectedIndex() ? 'bg-blue-500 text-white' : 'text-gray-900 hover:bg-gray-50'}`} href={result.link}>{result.identifier}</a>
						)))}
					</div>
				))}
			</div>
			<div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm" data-testid="random-examples">
				<span class="text-gray-400">Examples:</span>
				{search.randomExamples.map((example) => (
					<a target={examplesTarget} class="text-blue-600 hover:text-blue-800" href={example.link}>{example.identifier}</a>
				))}
			</div>
		</div>
	));
}
