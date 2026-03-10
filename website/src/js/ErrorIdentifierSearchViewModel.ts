import * as ko from 'knockout';

declare const __ERRORS_IDENTIFIERS_JSON__: Record<string, Record<string, Record<string, string[]>>>;

declare global {
	interface Window {
		__EXCLUDED_ERROR_IDENTIFIERS__?: string[];
	}
}

interface SearchResult {
	identifier: string;
	link: string;
}

export class ErrorIdentifierSearchViewModel {

	query: ko.Observable<string>;
	results: ko.PureComputed<SearchResult[]>;
	selectedIndex: ko.Observable<number>;
	showDropdown: ko.Observable<boolean>;
	randomExamples: SearchResult[];

	private allIdentifiers: string[];
	private hideTimer: number | null;

	constructor() {
		this.query = ko.observable('');
		this.selectedIndex = ko.observable(-1);
		this.showDropdown = ko.observable(false);
		this.hideTimer = null;

		this.allIdentifiers = Object.keys(__ERRORS_IDENTIFIERS_JSON__).sort();

		// Pick 5 random identifiers as examples, excluding unlikely/infeasible ones
		const excluded = new Set(window.__EXCLUDED_ERROR_IDENTIFIERS__ || []);
		const shuffled = this.allIdentifiers.filter((id) => !excluded.has(id));
		for (let i = shuffled.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			const tmp = shuffled[i];
			shuffled[i] = shuffled[j];
			shuffled[j] = tmp;
		}
		this.randomExamples = shuffled.slice(0, 5).sort().map((id) => ({
			identifier: id,
			link: '/error-identifiers/' + id,
		}));

		this.results = ko.pureComputed(() => {
			const q = this.query().trim().toLowerCase();
			if (q.length === 0) {
				return [];
			}

			const matches: SearchResult[] = [];
			for (let i = 0; i < this.allIdentifiers.length; i++) {
				if (this.allIdentifiers[i].toLowerCase().indexOf(q) !== -1) {
					matches.push({
						identifier: this.allIdentifiers[i],
						link: '/error-identifiers/' + this.allIdentifiers[i],
					});
					if (matches.length >= 10) {
						break;
					}
				}
			}

			return matches;
		});

		this.results.subscribe((results) => {
			this.selectedIndex(-1);
			if (results.length > 0) {
				this.showDropdown(true);
			}
		});
	}

	onFocus = (): void => {
		if (this.hideTimer !== null) {
			clearTimeout(this.hideTimer);
			this.hideTimer = null;
		}
		if (this.results().length > 0) {
			this.showDropdown(true);
		}
	};

	onBlur = (): void => {
		// Delay hiding so that clicks on links in the dropdown can land
		this.hideTimer = window.setTimeout(() => {
			this.showDropdown(false);
			this.hideTimer = null;
		}, 200);
	};

	onKeyDown = (_data: unknown, event: KeyboardEvent): boolean => {
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
