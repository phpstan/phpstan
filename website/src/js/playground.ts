import $ from 'jquery';
import * as ko from 'knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import './handlers';
import * as Sentry from '@sentry/browser';

Sentry.init({
	dsn: 'https://52d55b7d270244d99543220b548fed80@o190387.ingest.sentry.io/4505197959184384',
});

$(() => {

	const playgroundViewModel = new PlaygroundViewModel(window.location.pathname);
	playgroundViewModel.init(() => {
		ko.applyBindings(playgroundViewModel);
	});

});
