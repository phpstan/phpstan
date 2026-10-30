import ko from '@tko/build.reference';
import {when} from './tsx';

declare const __ERRORS_IDENTIFIERS_JSON__: Record<string, Record<string, Record<string, string[]>>>;
declare const __EXCLUDED_ERROR_IDENTIFIERS_JSON__: string[];

interface SearchResult {
	identifier: string;
	link: string;
}

const allIdentifiers = Object.keys(__ERRORS_IDENTIFIERS_JSON__).sort();

function searchResult(identifier: string): SearchResult {
	return {identifier, link: `/error-identifiers/${identifier}`};
}

// The search box and random examples shown on /documentation and /error-identifiers.
export class ErrorIdentifierSearch extends ko.Component {
	static override readonly customElementName = 'error-identifier-search';

	readonly query = ko.observable('');
	readonly selectedIndex = ko.observable(-1);
	readonly showDropdown = ko.observable(false);

	readonly results = ko.pureComputed(() => {
		const q = this.query().trim().toLowerCase();
		if (q.length === 0) {
			return [];
		}

		const matches: SearchResult[] = [];
		for (const identifier of allIdentifiers) {
			if (identifier.toLowerCase().includes(q)) {
				matches.push(searchResult(identifier));
				if (matches.length >= 10) {
					break;
				}
			}
		}

		return matches;
	});

	readonly randomExamples: SearchResult[];

	private hideTimer: number | null = null;

	constructor(private readonly examplesTarget?: string) {
		super();

		// Pick 5 random identifiers as examples, excluding unlikely/infeasible ones
		const excluded = new Set(__EXCLUDED_ERROR_IDENTIFIERS_JSON__);
		const shuffled = allIdentifiers.filter((id) => !excluded.has(id));
		for (let i = shuffled.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
		}
		this.randomExamples = shuffled.slice(0, 5).sort().map(searchResult);

		this.subscribe(this.results, (results) => {
			this.selectedIndex(-1);
			if (results.length > 0) {
				this.showDropdown(true);
			}
		});
	}

	get template(): JSX.Element {
		return (
			<div>
				<div class="relative max-w-md">
					<input id="identifier-search" type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-sm font-mono focus:border-blue-500 focus:outline-none transition duration-150" placeholder="e.g. argument.type, return.missing, deadCode..." ko-textInput={this.query} ko-event={{keydown: this.onKeyDown, focus: this.onFocus, blur: this.onBlur}} autocomplete="off" />
					{when(this.showDropdown, () => (
						<div class="absolute z-30 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
							{ko.pureComputed(() => this.results().map((result, index) => (
								<a class={`block px-4 py-2.5 font-mono text-sm no-underline transition duration-100 ${index === this.selectedIndex() ? 'bg-blue-500 text-white' : 'text-gray-900 hover:bg-gray-50'}`} href={result.link}>{result.identifier}</a>
							)))}
						</div>
					))}
				</div>
				<div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm" data-testid="random-examples">
					<span class="text-gray-400">Examples:</span>
					{this.randomExamples.map((example) => (
						<a target={this.examplesTarget} class="text-blue-600 hover:text-blue-800" href={example.link}>{example.identifier}</a>
					))}
				</div>
			</div>
		);
	}

	private readonly onFocus = (): void => {
		if (this.hideTimer !== null) {
			clearTimeout(this.hideTimer);
			this.hideTimer = null;
		}
		if (this.results().length > 0) {
			this.showDropdown(true);
		}
	};

	private readonly onBlur = (): void => {
		// Delay hiding so that clicks on links in the dropdown can land
		this.hideTimer = window.setTimeout(() => {
			this.showDropdown(false);
			this.hideTimer = null;
		}, 200);
	};

	private readonly onKeyDown = (_data: unknown, event: KeyboardEvent): boolean => {
		const results = this.results();
		const idx = this.selectedIndex();

		if (event.key === 'ArrowDown') {
			if (idx < results.length - 1) {
				this.selectedIndex(idx + 1);
			}
			return false;
		}

		if (event.key === 'ArrowUp') {
			if (idx > 0) {
				this.selectedIndex(idx - 1);
			}
			return false;
		}

		if (event.key === 'Enter') {
			if (idx >= 0 && idx < results.length) {
				window.location.href = results[idx].link;
			} else if (results.length > 0) {
				window.location.href = results[0].link;
			}
			return false;
		}

		if (event.key === 'Escape') {
			this.showDropdown(false);
			(event.target as HTMLElement).blur();
			return false;
		}

		return true;
	};
}
