import * as ko from 'knockout';
import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers, Decoration, DecorationSet} from '@codemirror/view'
import {EditorState, RangeSetBuilder, StateField, StateEffect, StateEffectType, Text} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'
import { PHPStanError } from './PHPStanError';

const buildErrorLines = (doc: Text, lines: number[]) => {
	const errorLineDecoration = Decoration.line({class: 'bg-red-200/50'});
	const builder = new RangeSetBuilder<Decoration>();
	for (let i = 0; i < doc.lines; i++) {
		const line = doc.line(i + 1);
		if (lines.includes(line.number - 1)) {
			builder.add(line.from, line.from, errorLineDecoration);
		}
	}

	return builder.finish();
};

ko.bindingHandlers.codeMirror = {
	init: (element, valueAccessor, allBindings, viewModel, bindingContext) => {
		// from https://github.com/codemirror/basic-setup/blob/78d1a916147c8c19678838cbdbf9396a8d1a6460/src/codemirror.ts
		// options explained here: https://codemirror.net/docs/ref/

		const text: string = ko.unwrap(valueAccessor());
		const changeErrorLines: StateEffectType<DecorationSet> = StateEffect.define();
		const errors: PHPStanError[] = allBindings.get('codeMirrorErrors');
		const lines: number[] = [];
		for (const error of errors) {
			const line = error.line;
			if (line < 1) {
				continue;
			}
			lines.push(line - 1);
		}

		const errorLines = StateField.define<DecorationSet>({
			create() {
				const doc = Text.of(text.split('\n'));
				return buildErrorLines(doc, lines)
			},
			update(textLines, tr) {
				textLines = textLines.map(tr.changes);
				for (const effect of tr.effects) {
					if (effect.is(changeErrorLines)) {
						textLines = effect.value;
					}
				}
				return textLines;
			},
			provide: f => EditorView.decorations.from(f)
		})
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
				errorLines,
			],
		})

		const editor = new EditorView({
			state: startState,
			parent: element,
		});
		ko.utils.domData.set(element, 'codeMirror', editor);
		ko.utils.domData.set(element, 'codeMirrorChangeErrorLines', changeErrorLines);
	},
};

ko.bindingHandlers.codeMirrorErrors = {
	update: (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) => {
		const editor: EditorView = ko.utils.domData.get(element, 'codeMirror');
		const changeErrorLines: StateEffectType<DecorationSet> = ko.utils.domData.get(element, 'codeMirrorChangeErrorLines');
		const errors: PHPStanError[] = ko.unwrap(valueAccessor());
		const lines = [];
		for (const error of errors) {
			const line = error.line;
			if (line < 1) {
				continue;
			}
			lines.push(line - 1);
		}

		editor.dispatch({
			effects: changeErrorLines.of(buildErrorLines(editor.state.doc, lines)),
		});
	},
};
