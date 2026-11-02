import ko from '@tko/build.reference';
import {EditorView} from '@codemirror/view';
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers} from '@codemirror/view';
import {Compartment, EditorState, StateEffect} from '@codemirror/state';
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language';
import {defaultKeymap, history, historyField, historyKeymap, indentWithTab} from '@codemirror/commands';
import {closeBrackets, closeBracketsKeymap, completionKeymap} from '@codemirror/autocomplete';
import {highlightSelectionMatches} from '@codemirror/search';
import {php} from '@codemirror/lang-php';
import {unifiedMergeView} from '@codemirror/merge';
import {PHPStanError} from './PHPStanError';
import {ttcn} from './ttcn-theme';
import {errorsCompartment, errorsFacet, lineErrors, updateErrorsEffect} from './editor/errors';
import {hover} from './editor/hover';
import {materialDark} from './editor/darkTheme';
import {docBlockKeymap} from './editor/docBlock';
import {phpantomHover} from './editor/phpantomHover';
import {goToDefinition} from './editor/goToDefinition';
import {occurrenceHighlight, inlineRename} from './editor/occurrences';
import {phpantomLsp, PHP_URI} from './phpantom/lspClient';
import {xray, setXRayData, setXRayEnabled, setXRayBusy} from './editor/xray';
import {PlaygroundTabViewModel} from './PlaygroundTabViewModel';

interface CodeMirrorOptions {
	code: ko.Observable<string>;
	restoredState: any | null;
	onXRayToggle: (enabled: boolean) => void;
}

interface CodeMirrorXRayOptions {
	enabled: ko.Observable<boolean>;
	busy: ko.PureComputed<boolean>;
	tab: ko.PureComputed<PlaygroundTabViewModel | null>;
}

declare global {
	interface KoCustomBindings {
		/** The playground editor, kept in sync with the code observable. */
		'ko-codeMirror'?: CodeMirrorOptions;
		/** Errors shown in the editor; needs ko-codeMirror on the same element. */
		'ko-codeMirrorErrors'?: ko.Subscribable<PHPStanError[]>;
		/** AST X-Ray state; needs ko-codeMirror on the same element. */
		'ko-codeMirrorXRay'?: CodeMirrorXRayOptions;
		/** A read-only diff of the code before and after a fix; nothing while null. */
		'ko-codeMirrorDiff'?: ko.Subscribable<{original: string; fixed: string} | null>;
	}
}

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const {code, restoredState, onXRayToggle}: CodeMirrorOptions = valueAccessor();
		const text = code();
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');

		const themeCompartment = new Compartment();

		const extensions = [
			lineNumbers(),
			// highlightActiveLineGutter(),
			highlightSpecialChars({
				// eslint-disable-next-line no-control-regex -- matching control characters is the point
				addSpecialChars: /[\u0000-\u0008\u000b-\u001f\u007f-\u009f\u00a0\u00ad\u061c\u2000-\u200f\u2028\u2029\u2060\u2066-\u2069\ufeff\ufff9-\ufffc]/g,
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
				{key: 'Ctrl-r', run: inlineRename, preventDefault: true},
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
				if (!update.docChanged) {
					return;
				}

				code(update.state.doc.toString());
			}),
			errorsCompartment.of(errorsFacet.of(errors)),
			lineErrors,
			hover,
			phpantomHover,
			goToDefinition,
			occurrenceHighlight,
			phpantomLsp.plugin(PHP_URI, 'php'),
			xray({onToggle: onXRayToggle}),
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
			? EditorState.fromJSON(restoredState, {extensions}, {history: historyField})
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
		darkModeObserver.observe(document.documentElement, {attributes: true, attributeFilter: ['class']});

		ko.utils.domData.set(element, 'codeMirror', editor);
	},
};

ko.bindingHandlers.codeMirrorXRay = {
	update: (element, valueAccessor) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const raw: CodeMirrorXRayOptions = valueAccessor();
		const tab = ko.unwrap(raw.tab);
		const value = {
			enabled: ko.unwrap(raw.enabled),
			busy: ko.unwrap(raw.busy),
			data: tab !== null ? tab.xray() : null,
		};
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
		ko.utils.domData.set(element, 'codeMirrorXRayLast', value);
		if (effects.length > 0) {
			editor.dispatch({effects});
		}
	},
};

ko.bindingHandlers.codeMirrorErrors = {
	update: (element, valueAccessor) => {
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

// The fix dialog's diff, like the editor but read-only: removed lines are shown
// above the lines that replace them and long unchanged stretches are collapsed.
ko.bindingHandlers.codeMirrorDiff = {
	update: (element, valueAccessor) => {
		ko.utils.domData.get(element, 'codeMirrorDiffDestroy')?.();
		ko.utils.domData.set(element, 'codeMirrorDiffDestroy', null);

		const value: {original: string; fixed: string} | null = ko.unwrap(valueAccessor());
		if (value === null) {
			return;
		}

		// Made again each time the dialog opens, which also picks up the current theme:
		// the theme switcher cannot be used while the dialog is open.
		const view = new EditorView({
			state: EditorState.create({
				doc: value.fixed,
				extensions: [
					lineNumbers(),
					EditorState.readOnly.of(true),
					EditorView.editable.of(false),
					syntaxHighlighting(defaultHighlightStyle, {fallback: true}),
					// The removed lines are highlighted on their own, without the <?php before them,
					// which would make them HTML. Plain PHP highlights them as the code around them.
					php({plain: true}),
					EditorState.tabSize.of(4),
					EditorView.lineWrapping,
					EditorView.theme({
						'.cm-content': {lineHeight: '1.6'},
						'& .cm-changeGutter': {width: '3px', paddingLeft: '0'},
						'& .cm-collapsedLines': {fontSize: '13px'},
					}),
					// Whole lines tinted like a diff on GitHub, the changed parts a little stronger.
					EditorView.baseTheme({
						'&light.cm-merge-b .cm-changedLine': {backgroundColor: '#e6ffec'},
						'&light.cm-merge-b .cm-changedText': {background: '#abf2bc'},
						'&light .cm-deletedChunk': {backgroundColor: '#ffebe9'},
						'&light.cm-merge-b .cm-deletedText, &light .cm-deletedChunk .cm-deletedText': {background: '#ffcecb'},
						'&light.cm-merge-b .cm-changedLineGutter': {background: '#2da44e'},
						'&light .cm-deletedLineGutter': {background: '#cf222e'},
						'&dark.cm-merge-b .cm-changedLine': {backgroundColor: 'rgb(46 160 67 / 0.15)'},
						'&dark.cm-merge-b .cm-changedText': {background: 'rgb(46 160 67 / 0.3)'},
						'&dark .cm-deletedChunk': {backgroundColor: 'rgb(248 81 73 / 0.12)'},
						'&dark.cm-merge-b .cm-deletedText, &dark .cm-deletedChunk .cm-deletedText': {background: 'rgb(248 81 73 / 0.22)'},
						'&dark.cm-merge-b .cm-changedLineGutter': {background: '#3fb950'},
						'&dark .cm-deletedLineGutter': {background: '#f85149'},
					}),
					document.documentElement.classList.contains('dark') ? materialDark : ttcn,
					unifiedMergeView({
						original: value.original,
						mergeControls: false,
						collapseUnchanged: {margin: 3, minSize: 4},
					}),
				],
			}),
			parent: element,
		});
		ko.utils.domData.set(element, 'codeMirrorDiffDestroy', () => view.destroy());
	},
};
