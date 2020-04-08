import * as ko from 'knockout';
import docsearch from 'docsearch.js/dist/cdn/docsearch.min.js';
import $ from 'jquery';

$(() => {
	docsearch({
		apiKey: '8a2169842d3555d81852e08de53b05fd',
		indexName: 'phpstan',
		inputSelector: '#searchInput',
		debug: false // Set debug to true if you want to inspect the dropdown
	});
});

export class MainMenuViewModel {

	mainMenuOpen: KnockoutObservable<boolean>;
	sidebarOpen: KnockoutObservable<boolean>;

	constructor() {
		this.mainMenuOpen = ko.observable(false);
		this.sidebarOpen = ko.observable(false);
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

}
