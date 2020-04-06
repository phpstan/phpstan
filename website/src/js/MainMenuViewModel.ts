import * as ko from 'knockout';

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
