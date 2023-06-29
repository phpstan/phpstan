import $ from 'jquery';
import * as ko from 'knockout';
import {MainMenuViewModel} from './MainMenuViewModel';
import {IgnoreErrorsWidgetModel} from './IgnoreErrorsWidgetModel';
import Prism from 'prismjs';
import 'prismjs/components/prism-yaml';

Prism.manual = true;

const resizeToFitContent = (el: HTMLElement) => {
	// http://stackoverflow.com/a/995374/3297291
	el.style.height = '1px';
	el.style.height = el.scrollHeight + 'px';
}

ko.bindingHandlers.autoResize = {
	init: (element, valueAccessor) => {
		ko.computed(() => {
			ko.unwrap(valueAccessor());
			resizeToFitContent(element);
		})
	}
};

ko.bindingHandlers.highlight = {
	update: (element, valueAccessor) => {
		const value = ko.unwrap(valueAccessor());

		if (value !== undefined) { // allows highlighting static code
			ko.utils.setTextContent(element, value);
		}

		Prism.highlightElement(element);
	}
};

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
		ignoreErrors: new IgnoreErrorsWidgetModel(),
	});
});
