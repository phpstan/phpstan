import * as ko from 'knockout';

export class MainMenuViewModel {

	mainMenuOpen: KnockoutObservable<boolean>;

	constructor() {
		this.mainMenuOpen = ko.observable(false);
	}

	toggleMainMenu(): void {
		this.mainMenuOpen(!this.mainMenuOpen());
	}

}
