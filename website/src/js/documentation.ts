import $ from 'jquery';
import ko from '@tko/build.knockout';
import {MainMenuViewModel} from './MainMenuViewModel';
import {ErrorIdentifierSearchViewModel} from './ErrorIdentifierSearchViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
		identifierSearch: new ErrorIdentifierSearchViewModel(),
	});
});
