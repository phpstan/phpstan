import * as ko from 'knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers, Decoration} from '@codemirror/view'
import {EditorState, RangeSet, RangeSetBuilder, Compartment} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const errorLines = new Compartment();
		const startState = EditorState.create({
			doc: ko.unwrap(valueAccessor()),
			extensions: [
				lineNumbers(),
				// highlightActiveLineGutter(),
				highlightSpecialChars(),
				history(),
				// foldGutter(),
				drawSelection(),
				// dropCursor(),
				// EditorState.allowMultipleSelections.of(true),
				indentOnInput(),
				syntaxHighlighting(defaultHighlightStyle, {fallback: true}),
				bracketMatching(),
				closeBrackets(),
				// autocompletion(),
				// rectangularSelection(),
				// crosshairCursor(),
				// highlightActiveLine(),
				// highlightSelectionMatches(),
				keymap.of([
					indentWithTab,
					...closeBracketsKeymap,
					...defaultKeymap,
					// ...searchKeymap,
					...historyKeymap,
					// ...foldKeymap,
					// ...completionKeymap,
					// ...lintKeymap
				]),
				php(),
				EditorState.tabSize.of(4),
				indentUnit.of('\t'),
				EditorView.lineWrapping,
				EditorView.updateListener.of((update) => {
					if (!update.docChanged) {
						return;
					}

					const observable = valueAccessor();
					observable(update.state.doc.toString());
				}),
				errorLines.of(EditorView.decorations.of(RangeSet.empty)),
			],
		})

		const editor = new EditorView({
			state: startState,
			parent: element,
		});
		ko.utils.domData.set(element, 'codeMirror', editor);
		ko.utils.domData.set(element, 'codeMirrorErrorLines', errorLines);
	},
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const newValue = ko.unwrap(valueAccessor());

		editor.dispatch({
			changes: {from: 0, to: editor.state.doc.length, insert: newValue},
		});
	},
};

ko.bindingHandlers.codeMirrorLines = {
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const errorLines: Compartment = ko.utils.domData.get(element, 'codeMirrorErrorLines');
		const newLines = ko.unwrap(valueAccessor());
		const errorLineDecoration = Decoration.line({class: 'bg-red-100'});
		const builder = new RangeSetBuilder<Decoration>();

		for (const {from, to} of editor.visibleRanges) {
			for (let pos = from; pos <= to;) {
				const line = editor.state.doc.lineAt(pos);
				if (newLines.includes(line.number - 1)) {
					builder.add(line.from, line.from, errorLineDecoration);
				}
				pos = line.to + 1;
			}
		}

		editor.dispatch({
			effects: errorLines.reconfigure(EditorView.decorations.of(builder.finish())),
		});
	},
};
