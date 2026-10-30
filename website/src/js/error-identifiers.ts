import $ from 'jquery';
import {initMainMenu} from './MainMenu';
import {initErrorIdentifierSearch} from './ErrorIdentifierSearch';

$(() => {
	initMainMenu();
	initErrorIdentifierSearch();
});
