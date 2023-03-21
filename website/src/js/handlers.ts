import * as ko from 'knockout';
import * as CodeMirror from 'codemirror';

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		const editorSettings: any = {
			mode: 'application/x-httpd-php',
			lineSeparator: '\n',
			indentWithTabs: true,
			indentUnit: 4,
			lineNumbers: true,
			matchBrackets: true,
			theme: 'ttcn',
			inputStyle: 'contenteditable',
			viewportMargin: Infinity,
			lineWrapping: true,
		};
		const codeMirror: any = CodeMirror.fromTextArea(element, editorSettings);
		codeMirror.getDoc().on('change', () => {
			const observable = valueAccessor();
			observable(codeMirror.getDoc().getValue());
		});
		codeMirror.getDoc().addLineClass(3, 'background', 'bg-red-500');

		ko.utils.domData.set(element, 'codeMirror', codeMirror);
	},
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const codeMirror: any = ko.utils.domData.get(element, 'codeMirror');

		const doc = codeMirror.getDoc();
		const oldValue = doc.getValue();
		const newValue = ko.unwrap(valueAccessor());
		if (oldValue === newValue) {
			return;
		}
		codeMirror.getDoc().setValue(newValue);
	},
};

ko.bindingHandlers.codeMirrorLines = {
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const codeMirror: any = ko.utils.domData.get(element, 'codeMirror');
		const doc = codeMirror.getDoc();
		const lineCount = doc.lineCount();
		for (let i = 0; i < lineCount; i++) {
			doc.removeLineClass(i, 'background');
		}

		const newLines = ko.unwrap(valueAccessor());
		for (const newLine of newLines) {
			doc.addLineClass(newLine, 'background', 'bg-red-100');
		}
	},
};
