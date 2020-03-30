import $ from 'jquery';
import * as ko from 'knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/php/php';
import './handlers';

$(() => {

	const playgroundViewModel = new PlaygroundViewModel();
	playgroundViewModel.init(window.location.pathname, () => {
		ko.applyBindings(playgroundViewModel);
	});

});
