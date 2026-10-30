import $ from 'jquery';
import {initMainMenu} from './MainMenu';
import {initIgnoreErrorsWidget} from './IgnoreErrorsWidget';

$(() => {
	initMainMenu();
	initIgnoreErrorsWidget();
});
