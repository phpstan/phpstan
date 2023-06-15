import $ from 'jquery';
import * as ko from 'knockout';
import { ErrorIdentifiersViewModel } from './ErrorIdentifiersViewModel';

$(() => {
	const hash = window.location.hash;
	ko.applyBindings(new ErrorIdentifiersViewModel(hash));

	let currentElement: HTMLElement | null = null;
	if (hash !== '') {
		const element = document.getElementById(hash.slice(1));
		if (element !== null) {
			element.scrollIntoView();
			element.className = 'hash-target';
			currentElement = element;
		}
	}

	window.onhashchange = () => {
		if (currentElement !== null) {
			currentElement.className = '';
		}
	};
});
