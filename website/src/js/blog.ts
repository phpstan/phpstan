import $ from 'jquery';
import ko from '@tko/build.knockout';
import {MainMenuViewModel} from './MainMenuViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
	});
});
