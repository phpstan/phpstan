import * as ko from 'knockout';
import {EditorView, hoverTooltip} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers, Decoration, DecorationSet} from '@codemirror/view'
import {EditorState, RangeSetBuilder, StateField, StateEffect, StateEffectType, Text} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'
import { PHPStanError } from './PHPStanError';
import { ttcn } from './ttcn-theme';

const buildErrorLines = (doc: Text, errors: PHPStanError[]) => {
	const errorLineDecoration = Decoration.line({class: 'bg-red-200/50'});
	const tipLineDecoration = Decoration.line({class: 'bg-yellow-200/50'});
	const builder = new RangeSetBuilder<Decoration>();
	const errorLines: number[] = [];
	const tipLines: number[] = [];
	for (const error of errors) {
		if (error.line < 1) {
			continue;
		}
		if (error.identifier && error.identifier.startsWith('phpstanPlayground.')) {
			tipLines.push(error.line - 1);
		} else {
			errorLines.push(error.line - 1);
		}
	}

	for (let i = 0; i < doc.lines; i++) {
		const line = doc.line(i + 1);
		if (errorLines.includes(line.number - 1)) {
			builder.add(line.from, line.from, errorLineDecoration);
		} else if (tipLines.includes(line.number - 1)) {
			builder.add(line.from, line.from, tipLineDecoration);
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

		const hover = hoverTooltip((view, pos, side) => {
			const currentErrors: PHPStanError[] = allBindings.get('codeMirrorErrors');
			const line = view.state.doc.lineAt(pos);
			const lineErrors: PHPStanError[] = [];
			for (const error of currentErrors) {
				if (error.line === line.number) {
					lineErrors.push(error);
				}
			}

			if (lineErrors.length === 0) {
				return null;
			}

			let from = line.from;
			while (from < line.to && /\s/.test(line.text[from - line.from])) {
				from++
			}

			if (from === line.to) {
				from = line.from
			}

			return {
				pos: from,
				end: line.to,
				above: true,
				create() {
					const dom = document.createElement('div')
					let style = 'bg-yellow-100 text-black';
					for (const error of lineErrors) {
						const errorDom = document.createElement('div');
						errorDom.className = '';
						errorDom.textContent = error.message;
						dom.appendChild(errorDom);

						if (!error.identifier || !error.identifier.startsWith('phpstanPlayground.')) {
							style = 'bg-red-800 text-white';
						}
					}

					dom.className = 'rounded-lg px-3 py-1 border-0 text-xs flex flex-col gap-2 ' + style;

					const outerDom = document.createElement('div');
					outerDom.className = 'pb-1';
					outerDom.appendChild(dom);

					return {
						dom: outerDom,
					}
				}
			}
		}, {
			hoverTime: 5,
		});

		const errorLines = StateField.define<DecorationSet>({
			create() {
				const doc = Text.of(text.split('\n'));
				return buildErrorLines(doc, errors)
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
				hover,
				ttcn,
				EditorView.baseTheme({
					'.cm-tooltip.cm-tooltip-hover': {
						border: 'none',
					},
				}),
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

		editor.dispatch({
			effects: changeErrorLines.of(buildErrorLines(editor.state.doc, errors)),
		});
	},
};
