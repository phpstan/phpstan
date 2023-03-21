import $ from 'jquery';
import * as ko from 'knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import './handlers';

$(() => {

	const playgroundViewModel = new PlaygroundViewModel();
	playgroundViewModel.init(window.location.pathname, () => {
		ko.applyBindings(playgroundViewModel);
	});

});
