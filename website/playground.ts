import $ from 'jquery';
import * as ko from 'knockout';
import {PlaygroundViewModel} from './js/PlaygroundViewModel';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/php/php';
import './js/handlers';

$(() => {

	const rootViewModel = new PlaygroundViewModel();
	rootViewModel.init(window.location.pathname, () => {
		ko.applyBindings(rootViewModel);
	});

});
