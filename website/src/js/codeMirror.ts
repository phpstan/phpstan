import * as ko from 'knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers} from '@codemirror/view'
import {EditorState} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'
import { PHPStanError } from './PHPStanError';
import { ttcn } from './ttcn-theme';
import {errorsCompartment, errorsFacet, lineErrors, updateErrorsEffect} from "./editor/errors";
import {hover} from "./editor/hover";
import {Theme} from "./ThemeSwitcher";

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const text: string = ko.unwrap(valueAccessor());
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');

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
				ttcn,
				EditorView.baseTheme({
					'.cm-tooltip.cm-tooltip-hover': {
						border: 'none',
						background: 'transparent',
					},
				}),
				// TODO Here we should switch to a dark theme when ThemeSwitcher.get() returns 'dark'.
				//      I gave up digging through CM6's mess to figure this part out... it seems it has functionality
				//      for dark mode and at the same time it doesn't. 🤯
				//      - https://codemirror.net/docs/ref/#view.EditorView^darkTheme
				//      - https://discuss.codemirror.net/t/dynamic-light-mode-dark-mode-how/4709/4
				//      - https://discuss.codemirror.net/t/cm6-dynamically-switching-syntax-theme-w-reconfigure/2858/7
			],
		})

		const editor = new EditorView({
			state: startState,
			parent: element,
		});
		ko.utils.domData.set(element, 'codeMirror', editor);

		window.addEventListener('themechange', (ev: CustomEvent<Theme>): void => {
			// TODO From my understanding, we need to "unload" or "load" a dark theme extension depending on ev.detail.
			//      editor.dispatch({effects: ????.reconfigure([???????])});
		});
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
