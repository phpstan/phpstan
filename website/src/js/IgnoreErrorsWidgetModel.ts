import * as ko from 'knockout';
import $ from 'jquery';

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
			xhr = $.get('https://ymln7gojyg.execute-api.eu-west-1.amazonaws.com/ignoreErrors', {
				message: this.message(),
				path: this.path(),
			}).done(((data) => {
				this.result(data.neon.trim());

				const anyWindow = (window as any);
				if (typeof anyWindow.fathom !== 'undefined') {
					anyWindow.fathom.trackGoal('ESXYRAMV', 0);
				}
			})).fail((xhrr, textStatus) => {
				if (textStatus === 'abort') {
					return;
				}

				this.hasServerError(true);
			}).always(() => {
				this.loading(false);
				xhr = null;
			});
		});
	}

}
