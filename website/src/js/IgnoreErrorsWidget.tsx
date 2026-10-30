import ko from '@tko/build.reference';
import Prism from 'prismjs';
import 'prismjs/components/prism-yaml';
import {IgnoreErrorsWidgetModel} from './IgnoreErrorsWidgetModel';
import {mount, when} from './tsx';

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
export function initIgnoreErrorsWidget(): void {
	const widget = new IgnoreErrorsWidgetModel();

	mount('ignore-errors-widget', (
		<div>
			<p><label for="ignore-message" class="block text-md font-medium text-gray-700">Error message</label></p>
			<textarea rows="1" name="ignore-message" id="ignore-message" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-md border-gray-300 rounded-md font-mono resize-none placeholder:italic placeholder:text-slate-400" ko-textInput={widget.message} ko-autoResize={widget.message} placeholder="Error message"></textarea>

			<p><label for="ignore-path" class="block text-md font-medium text-gray-700 mt-8">Optional path</label></p>
			<textarea rows="1" name="ignore-path" id="ignore-path" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-md border-gray-300 rounded-md font-mono resize-none placeholder:italic placeholder:text-slate-400" ko-textInput={widget.path} ko-autoResize={widget.path} placeholder="path/to/File.php"></textarea>

			<p><span class="block text-md font-medium text-gray-700 mt-8">Copy the following to your phpstan.neon:</span></p>

			{when(widget.hasServerError, () => (
				<p><span class="block text-red-500">An error occurred. Please try again later.</span></p>
			))}

			<pre class="language-yaml" ko-highlight={widget.result}></pre>
		</div>
	));
}
