import $ from 'jquery';
import * as ko from 'knockout';
import {MainMenuViewModel} from './MainMenuViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
	});
});
