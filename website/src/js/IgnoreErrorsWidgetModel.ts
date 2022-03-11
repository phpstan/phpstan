import * as ko from 'knockout';
import $ from 'jquery';
import Prism from 'prismjs';

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
			element.innerHTML = value;
		}

		Prism.highlightElement(element);
	}

};

export class IgnoreErrorsWidgetModel {

	message: ko.Observable<string>;
	path: ko.Observable<string>;
	loading: ko.Observable<boolean>;
	hasServerError: ko.Observable<boolean>;
	result: ko.Observable<string>;

	constructor() {
		this.message = ko.observable('Access to an undefined property App\\Foo::$bar.');
		this.path = ko.observable('');
		this.result = ko.observable(`parameters:
	ignoreErrors:
		- '#^Access to an undefined property App\\\\Foo\\:\\:\\$bar\\.$#'`);
		this.loading = ko.observable<boolean>(false);
		this.hasServerError = ko.observable<boolean>(false);

		let xhr: JQuery.jqXHR | null = null;
		ko.computed(() => {
			return this.message() + '---' + this.path();
		}).subscribe(() => {
			this.loading(true);
			this.hasServerError(false);
			if (xhr !== null) {
				xhr.abort();
				xhr = null;
			}
		});

		ko.pureComputed(() => {
			return this.message() + '---' + this.path();
		}).extend({
			notify: 'always',
			rateLimit: { timeout: 500, method: 'notifyWhenChangesStop' },
		}).subscribe(() => {
			$.get('https://ymln7gojyg.execute-api.eu-west-1.amazonaws.com/ignoreErrors', {
				message: this.message(),
				path: this.path(),
			}).done(((data) => {
				this.result(data.neon.trim());
			})).fail((xhrr, textStatus) => {
				if (textStatus === 'abort') {
					return;
				}

				this.hasServerError(true);
			}).always(() => {
				this.loading(false);
			});
		});
	}

}
