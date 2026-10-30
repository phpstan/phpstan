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

export class MainMenuViewModel {

	readonly mainMenuOpen = ko.observable(false);
	readonly sidebarOpen = ko.observable(false);

	constructor() {
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

}
