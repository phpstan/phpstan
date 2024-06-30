import * as ko from 'knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers} from '@codemirror/view'
import {Compartment, EditorState} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'
import { PHPStanError } from './PHPStanError';
import { ttcn } from './ttcn-theme';
import {errorsCompartment, errorsFacet, lineErrors, updateErrorsEffect} from "./editor/errors";
import {hover} from "./editor/hover";
import {materialDark} from "./editor/darkTheme";

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const text: string = ko.unwrap(valueAccessor());
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');

		const themeCompartment = new Compartment();

		const startState = EditorState.create({
			doc: text,
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
				errorsCompartment.of(errorsFacet.of(errors)),
				lineErrors,
				hover,
				EditorView.baseTheme({
					'.cm-tooltip.cm-tooltip-hover': {
						border: 'none',
						background: 'transparent',
					},
				}),
				themeCompartment.of(
					document.documentElement.classList.contains('dark')
						? [materialDark]
						: [ttcn],
				),
			],
		})

		const editor = new EditorView({
			state: startState,
			parent: element,
		});

		const darkModeObserver = new MutationObserver((mutationsList) => {
			for (const mutation of mutationsList) {
				if (mutation.type === 'attributes') {
					editor.dispatch({
						effects: themeCompartment.reconfigure(
							document.documentElement.classList.contains('dark')
								? [materialDark]
								: [ttcn],
						),
					});
				}
			}
		});
		darkModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

		ko.utils.domData.set(element, 'codeMirror', editor);
	},
};

ko.bindingHandlers.codeMirrorErrors = {
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const errors: PHPStanError[] = ko.unwrap(valueAccessor());

		editor.dispatch({
			effects: [
				errorsCompartment.reconfigure(errorsFacet.of(errors)),
				updateErrorsEffect.of(true),
			],
		});
	},
};
