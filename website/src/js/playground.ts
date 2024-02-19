import $ from 'jquery';
import * as ko from 'knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import './codeMirror';
import * as Sentry from '@sentry/browser';
import fs from 'fs';

const release = fs.readFileSync(__dirname + '/../release.txt', 'utf8');

Sentry.init({
	dsn: 'https://52d55b7d270244d99543220b548fed80@o190387.ingest.sentry.io/4505197959184384',
	release: release
});

$(() => {

	const playgroundViewModel = new PlaygroundViewModel(window.location.pathname);
	playgroundViewModel.init(() => {
		ko.applyBindings(playgroundViewModel);
	});

});
