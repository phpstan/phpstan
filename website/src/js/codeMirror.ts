import * as ko from 'knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers} from '@codemirror/view'
import {Compartment, EditorState} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyField, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap, completionKeymap} from '@codemirror/autocomplete'
import {highlightSelectionMatches} from '@codemirror/search'
import {php} from '@codemirror/lang-php'
import { PHPStanError } from './PHPStanError';
import { ttcn } from './ttcn-theme';
import {errorsCompartment, errorsFacet, lineErrors, updateErrorsEffect} from "./editor/errors";
import {hover} from "./editor/hover";
import {materialDark} from "./editor/darkTheme";
import {urlIdField, urlIdExtensions} from "./editor/urlId";
import {docBlockKeymap} from "./editor/docBlock";
import {phpantomHover} from "./editor/phpantomHover";
import {goToDefinition} from "./editor/goToDefinition";
import {phpantomLsp, PHP_URI} from "./phpantom/lspClient";

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const text: string = ko.unwrap(valueAccessor());
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');
		const initialUrlId: string | null = allBindings.get('codeMirrorInitialUrlId') ?? null;
		const urlIdChange: ((id: string | null) => void) | null = allBindings.get('codeMirrorUrlIdChange') ?? null;
		const restoredState: any | null = allBindings.get('codeMirrorRestoredState') ?? null;

		const themeCompartment = new Compartment();

		const extensions = [
			lineNumbers(),
			// highlightActiveLineGutter(),
			highlightSpecialChars({
				addSpecialChars: /[\u0000-\u0008\u000b-\u001f\u007f-\u009f\u00a0\u00ad\u061c\u2000-\u200f\u2028\u2029\u2060\u2066-\u2069\ufeff\ufff9-\ufffc]/g
			}),
			history(),
			// foldGutter(),
			drawSelection(),
			// dropCursor(),
			// EditorState.allowMultipleSelections.of(true),
			indentOnInput(),
			syntaxHighlighting(defaultHighlightStyle, {fallback: true}),
			bracketMatching(),
			closeBrackets(),
			highlightSelectionMatches(),
			// autocompletion(),
			// rectangularSelection(),
			// crosshairCursor(),
			// highlightActiveLine(),
			// highlightSelectionMatches(),
			keymap.of([
				...completionKeymap,
				...docBlockKeymap,
				indentWithTab,
				...closeBracketsKeymap,
				...defaultKeymap,
				// ...searchKeymap,
				...historyKeymap,
				// ...foldKeymap,
				// ...lintKeymap
			]),
			php(),
			EditorState.tabSize.of(4),
			indentUnit.of('\t'),
			EditorView.lineWrapping,
			EditorView.updateListener.of((update) => {
				if (urlIdChange) {
					const oldId = update.startState.field(urlIdField);
					const newId = update.state.field(urlIdField);
					if (oldId !== newId) {
						urlIdChange(newId);
					}
				}
				if (!update.docChanged) {
					return;
				}

				const observable = valueAccessor();
				observable(update.state.doc.toString());
			}),
			...urlIdExtensions(initialUrlId),
			errorsCompartment.of(errorsFacet.of(errors)),
			lineErrors,
			hover,
			phpantomHover,
			goToDefinition,
			phpantomLsp.plugin(PHP_URI, 'php'),
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
		];

		const startState = restoredState
			? EditorState.fromJSON(restoredState, {extensions}, {history: historyField, urlId: urlIdField})
			: EditorState.create({doc: text, extensions});

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
