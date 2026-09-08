import ko from '@tko/build.knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers} from '@codemirror/view'
import {Compartment, EditorState, StateEffect} from '@codemirror/state'
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
import {occurrenceHighlight, inlineRename} from "./editor/occurrences";
import {phpantomLsp, PHP_URI} from "./phpantom/lspClient";
import {xray, setXRayData, setXRayEnabled, setXRayBusy} from "./editor/xray";
import {XRayData} from "./XRayData";

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const text: string = ko.unwrap(valueAccessor());
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');
		const initialUrlId: string | null = allBindings.get('codeMirrorInitialUrlId') ?? null;
		const urlIdChange: ((id: string | null) => void) | null = allBindings.get('codeMirrorUrlIdChange') ?? null;
		const restoredState: any | null = allBindings.get('codeMirrorRestoredState') ?? null;
		const xrayToggle: ((enabled: boolean) => void) | null = allBindings.get('codeMirrorXRayToggle') ?? null;

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
				// Ctrl-R starts inline rename of the symbol under the cursor (all
				// occurrences become editable at once). preventDefault so it never
				// falls through to the browser's reload.
				{key: "Ctrl-r", run: inlineRename, preventDefault: true},
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
			occurrenceHighlight,
			phpantomLsp.plugin(PHP_URI, 'php'),
			xray({onToggle: (enabled) => xrayToggle?.(enabled)}),
			EditorView.baseTheme({
				'.cm-tooltip.cm-tooltip-hover': {
					border: 'none',
					background: 'transparent',
				},
			}),
			// A little airier than CodeMirror's 1.4, and the same with AST X-Ray on:
			// its boxes fit in this spacing, so the code stays put when it's toggled.
			EditorView.theme({
				'.cm-content': {lineHeight: '1.6'},
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

ko.bindingHandlers.codeMirrorXRay = {
	update: (element, valueAccessor) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const value: {enabled: boolean, busy: boolean, data: XRayData | null} = ko.unwrap(valueAccessor());
		const last: Partial<typeof value> = ko.utils.domData.get(element, 'codeMirrorXRayLast') ?? {};
		const effects: StateEffect<unknown>[] = [];
		if (last.enabled !== value.enabled) {
			effects.push(setXRayEnabled.of(value.enabled));
		}
		if (last.busy !== value.busy) {
			effects.push(setXRayBusy.of(value.busy));
		}
		// Only when the data itself changes: the editor keeps the node positions
		// mapped through edits, and re-sending the same data would reset them.
		if (last.data !== value.data) {
			effects.push(setXRayData.of(value.data));
		}
		ko.utils.domData.set(element, 'codeMirrorXRayLast', {...value});
		if (effects.length > 0) {
			editor.dispatch({effects});
		}
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
