import $ from 'jquery';
import ko from '@tko/build.knockout';
import {PlaygroundViewModel} from './PlaygroundViewModel';
import './codeMirror';
import Honeybadger from '@honeybadger-io/js';

declare const __RELEASE_VERSION__: string;

Honeybadger.configure({
	apiKey: import.meta.env.VITE_HONEYBADGER_API_KEY,
	environment: import.meta.env.PROD ? 'production' : 'development',
	revision: __RELEASE_VERSION__,
	ignoreBrowserExtensionErrors: true,
});

// A translator rewrites the DOM under CodeMirror and Knockout. Errors after that
// are not ours to fix.
Honeybadger.beforeNotify(() => {
	const html = document.documentElement;
	return html.lang === 'en' && !html.classList.contains('translated-ltr') && !html.classList.contains('translated-rtl');
});

$(() => {

	const playgroundViewModel = new PlaygroundViewModel(window.location.pathname);

	// Failed connections and timeouts are the visitor's network, 5xx are ours.
	$(document).ajaxError((event, xhr, settings) => {
		if (xhr.status < 500 || settings.url === undefined || !settings.url.startsWith(playgroundViewModel.apiBaseUrl)) {
			return;
		}

		Honeybadger.notify({
			name: 'PlaygroundApiError',
			message: `${settings.type} ${new URL(settings.url).pathname} responded with ${xhr.status}`,
			context: {
				url: settings.url,
				status: xhr.status,
				request: settings.data,
				response: xhr.responseText,
			},
		});
	});
	playgroundViewModel.init(() => {
		ko.applyBindings(playgroundViewModel);
	});

});
