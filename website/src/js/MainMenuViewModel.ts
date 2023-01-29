import * as ko from 'knockout';
import $ from 'jquery';
import littlefoot from 'littlefoot';
import docsearch from '@docsearch/js';
import '@docsearch/css';

$(() => {
	littlefoot();
});

export class MainMenuViewModel {

	mainMenuOpen: ko.Observable<boolean>;
	sidebarOpen: ko.Observable<boolean>;

	constructor() {
		this.mainMenuOpen = ko.observable<boolean>(false);
		this.sidebarOpen = ko.observable<boolean>(false);

		docsearch({
			container: '#docsearch',
			appId: '563YUB35R3',
			indexName: 'phpstan',
			apiKey: '38f6379285feb01cc915c6967c715ec2',
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

}
