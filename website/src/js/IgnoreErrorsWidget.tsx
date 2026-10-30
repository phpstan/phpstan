import ko from '@tko/build.reference';
import Prism from 'prismjs';
import 'prismjs/components/prism-yaml';
import $ from 'jquery';
import {when} from './tsx';

Prism.manual = true;

declare global {
	interface KoCustomBindings {
		/** Fits the textarea's height to its content whenever the observable changes. */
		'ko-autoResize'?: ko.Subscribable<string>;
		/** Shows the observable's text highlighted by Prism. */
		'ko-highlight'?: ko.Subscribable<string>;
	}
}

const resizeToFitContent = (el: HTMLElement) => {
	// http://stackoverflow.com/a/995374/3297291
	el.style.height = '1px';
	el.style.height = el.scrollHeight + 'px';
};

ko.bindingHandlers.autoResize = {
	init: (element, valueAccessor) => {
		ko.computed(() => {
			ko.unwrap(valueAccessor());
			resizeToFitContent(element);
		});
	},
};

ko.bindingHandlers.highlight = {
	update: (element, valueAccessor) => {
		ko.utils.setTextContent(element, ko.unwrap(valueAccessor()));
		Prism.highlightElement(element);
	},
};

// The ignoreErrors entry generator on /user-guide/ignoring-errors.
export class IgnoreErrorsWidget extends ko.Component {
	static override readonly customElementName = 'ignore-errors-widget';

	readonly message = ko.observable('Access to an undefined property App\\Foo::$bar.');
	readonly path = ko.observable('');
	readonly hasServerError = ko.observable(false);
	readonly result = ko.observable(`parameters:
	ignoreErrors:
		- '#^Access to an undefined property App\\\\Foo\\:\\:\\$bar\\.$#'`);

	constructor() {
		super();

		const input = this.computed(() => `${this.message()}---${this.path()}`);
		let xhr: JQuery.jqXHR | null = null;
		this.subscribe(input, () => {
			this.hasServerError(false);
			xhr?.abort();
			xhr = null;
		});

		const settledInput = this.computed(() => input()).extend({
			notify: 'always',
			rateLimit: {timeout: 500, method: 'notifyWhenChangesStop'},
		});
		this.subscribe(settledInput, () => {
			xhr = $.get('https://ymln7gojyg.execute-api.eu-west-1.amazonaws.com/ignoreErrors', {
				message: this.message(),
				path: this.path(),
			}).done((data) => {
				this.result(data.neon.trim());

				const anyWindow = window as any;
				if (typeof anyWindow.fathom !== 'undefined') {
					anyWindow.fathom.trackGoal('ESXYRAMV', 0);
				}
			}).fail((_xhr, textStatus) => {
				if (textStatus === 'abort') {
					return;
				}

				this.hasServerError(true);
			}).always(() => {
				xhr = null;
			});
		});
	}

	get template(): JSX.Element {
		return (
			<div>
				<p><label for="ignore-message" class="block text-md font-medium text-gray-700">Error message</label></p>
				<textarea rows="1" name="ignore-message" id="ignore-message" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-md border-gray-300 rounded-md font-mono resize-none placeholder:italic placeholder:text-slate-400" ko-textInput={this.message} ko-autoResize={this.message} placeholder="Error message"></textarea>

				<p><label for="ignore-path" class="block text-md font-medium text-gray-700 mt-8">Optional path</label></p>
				<textarea rows="1" name="ignore-path" id="ignore-path" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-md border-gray-300 rounded-md font-mono resize-none placeholder:italic placeholder:text-slate-400" ko-textInput={this.path} ko-autoResize={this.path} placeholder="path/to/File.php"></textarea>

				<p><span class="block text-md font-medium text-gray-700 mt-8">Copy the following to your phpstan.neon:</span></p>

				{when(this.hasServerError, () => (
					<p><span class="block text-red-500">An error occurred. Please try again later.</span></p>
				))}

				<pre class="language-yaml" ko-highlight={this.result}></pre>
			</div>
		);
	}
}
