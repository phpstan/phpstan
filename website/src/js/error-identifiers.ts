import $ from 'jquery';
import {initMainMenu} from './MainMenu';
import {ErrorIdentifierSearch} from './ErrorIdentifierSearch';
import {initErrorIdentifierGroups} from './ErrorIdentifierGroups';
import {mountComponent} from './tsx';

$(() => {
	initMainMenu();
	mountComponent('identifier-search-widget', ErrorIdentifierSearch);
	initErrorIdentifierGroups();
});
