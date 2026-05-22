import $ from 'jquery';
import ko from '@tko/build.knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import './codeMirror';
import * as Sentry from '@sentry/browser';

declare const __RELEASE_VERSION__: string;

Sentry.init({
	dsn: 'https://52d55b7d270244d99543220b548fed80@o190387.ingest.sentry.io/4505197959184384',
	release: __RELEASE_VERSION__
});

$(() => {

	const playgroundViewModel = new PlaygroundViewModel(window.location.pathname);
	playgroundViewModel.init(() => {
		ko.applyBindings(playgroundViewModel);
	});

});
