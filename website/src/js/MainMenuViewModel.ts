import ko from '@tko/build.reference';
import $ from 'jquery';
import littlefoot from 'littlefoot';
import docsearch from '@docsearch/js/docsearch';
import '@docsearch/css';

// Self-hosted Inter (variable font) via npm, replacing the external
// https://rsms.me/inter/inter.css stylesheet. SIL Open Font License 1.1.
// Imported here (the menu loads on every page) rather than in app.pcss so
// that Vite — not Tailwind's @import inliner — bundles and fingerprints the
// woff2 files. Exposes the "Inter Variable" family used by tailwind.config.js.
import '@fontsource-variable/inter/index.css';
import '@fontsource-variable/inter/wght-italic.css';

$(() => {
	littlefoot();
});

export type Theme = 'auto' | 'light' | 'dark';

export class MainMenuViewModel {

	theme: ko.Observable<Theme>;
	mainMenuOpen: ko.Observable<boolean>;
	sidebarOpen: ko.Observable<boolean>;

	private themeKey = 'phpstanWebTheme';

	constructor() {
		let theme: Theme = 'auto';
		if (localStorage.getItem(this.themeKey) !== null) {
			theme = localStorage.getItem(this.themeKey) as Theme;
		}
		this.theme = ko.observable(theme);

		this.mainMenuOpen = ko.observable<boolean>(false);
		this.sidebarOpen = ko.observable<boolean>(false);

		const docsearchOptions = {
			appId: '563YUB35R3',
			indices: ['phpstan'],
			apiKey: '38f6379285feb01cc915c6967c715ec2',
			transformItems: (items: any[]) => {
				return items.map((item) => {
					const query = document.querySelector<HTMLInputElement>('.DocSearch-Input')?.value.trim();
					const contentMatch = item._highlightResult?.content?.matchLevel !== undefined
						&& item._highlightResult.content.matchLevel !== 'none';
					if (query && item.url && !item.url.includes('#') && query.length >= 3 && item.type !== 'lvl1' && contentMatch) {
						item.url = item.url + '#:~:text=' + encodeURIComponent(query).replace(/-/g, '%2D');
					}
					return item;
				});
			},
		};
		docsearch({container: '#docsearch', ...docsearchOptions});
		docsearch({container: '#docsearch-mobile', ...docsearchOptions});
	}

	toggleMainMenu(): void {
		this.mainMenuOpen(!this.mainMenuOpen());
		if (this.mainMenuOpen()) {
			this.sidebarOpen(false);
		}
	}

	openSidebar(): void {
		this.mainMenuOpen(false);
		this.sidebarOpen(true);
	}

	closeSidebar(): void {
		this.sidebarOpen(false);
	}

	handleSidebarClick(event: MouseEvent): boolean {
		if ((event.target as HTMLElement).tagName === 'A') {
			this.sidebarOpen(false);
		}

		return true;
	}

	switchTheme(theme: Theme): void {
		this.theme(theme);
		if (theme === 'auto') {
			localStorage.removeItem(this.themeKey);
		} else {
			localStorage.setItem(this.themeKey, theme);
		}

		const dark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
		document.documentElement.classList.toggle('dark', dark);
	}

}
