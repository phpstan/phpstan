import * as ko from 'knockout';
import $ from 'jquery';
import littlefoot from 'littlefoot';
import docsearch from '@docsearch/js';
import '@docsearch/css';

$(() => {
	littlefoot();
});

type Theme = 'auto' | 'light' | 'dark';

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

		docsearch({
			container: '#docsearch',
			appId: '563YUB35R3',
			indexName: 'phpstan',
			apiKey: '38f6379285feb01cc915c6967c715ec2',
			searchParameters: {
				clickAnalytics: true,
			},
		});
		docsearch({
			container: '#docsearch-mobile',
			appId: '563YUB35R3',
			indexName: 'phpstan',
			apiKey: '38f6379285feb01cc915c6967c715ec2',
			searchParameters: {
				clickAnalytics: true,
			},
		});
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

	handleSidebarClick(viewModel: MainMenuViewModel, event: any): boolean {
		const target: HTMLElement = event.target;
		if (target.tagName !== 'A') {
			return true;
		}

		this.sidebarOpen(false);
		return true;
	}

	switchToAutoTheme(): void {
		this.theme('auto');
		localStorage.removeItem(this.themeKey);
		const query = window.matchMedia('(prefers-color-scheme: dark)');
		if (query.matches) {
			document.documentElement.classList.add('dark');
		} else {
			document.documentElement.classList.remove('dark');
		}
	}

	switchToLightTheme(): void {
		this.theme('light');
		localStorage.setItem(this.themeKey, 'light');
		document.documentElement.classList.remove('dark');
	}

	switchToDarkTheme(): void {
		this.theme('dark');
		localStorage.setItem(this.themeKey, 'dark');
		document.documentElement.classList.add('dark');
	}

}
