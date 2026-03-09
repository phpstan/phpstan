import $ from 'jquery';
import * as ko from 'knockout';
import {MainMenuViewModel} from './MainMenuViewModel';
import {ErrorIdentifierSearchViewModel} from './ErrorIdentifierSearchViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
		identifierSearch: new ErrorIdentifierSearchViewModel(),
	});
});
