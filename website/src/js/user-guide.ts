import $ from 'jquery';
import {initMainMenu} from './MainMenu';
import {IgnoreErrorsWidget} from './IgnoreErrorsWidget';
import {mountComponent} from './tsx';

$(() => {
	initMainMenu();
	mountComponent('ignore-errors-widget', IgnoreErrorsWidget);
});
