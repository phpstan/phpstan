import {EditorView} from '@codemirror/view'
import {keymap, highlightSpecialChars, drawSelection,
	lineNumbers, Decoration} from '@codemirror/view'
import {EditorState, RangeSet, RangeSetBuilder, Compartment} from '@codemirror/state'
import {defaultHighlightStyle, syntaxHighlighting, indentOnInput, indentUnit, bracketMatching} from '@codemirror/language'
import {defaultKeymap, history, historyKeymap, indentWithTab} from '@codemirror/commands'
import {closeBrackets, closeBracketsKeymap} from '@codemirror/autocomplete'
import {php} from '@codemirror/lang-php'


(() => {
	const errorLines = new Compartment();
	const startState = EditorState.create({
		doc: '<?php declare(strict_types = 1);\n' +
		'\n' +
		'class HelloWorld\n' +
		'{\n' +
		'\tpublic function sayHello(DateTimeImutable $date): void\n' +
		'\t{\n' +
		'\t\techo \'Hello, \' . $date->format(\'j. n. Y\');\n' +
		'\t}\n' +
		'}',
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
			/*EditorView.updateListener.of((update) => {
				if (!update.docChanged) {
					return;
				}

				const observable = valueAccessor();
				observable(update.state.doc.toString());
			}),*/
			errorLines.of(EditorView.decorations.of(RangeSet.empty)),
		],
	})

	const cm6 = document.getElementById('cm6');
	if (cm6 === null) {
		return;
	}

	const editor = new EditorView({
		state: startState,
		parent: cm6,
	});

	window.setTimeout(() => {
		const newLines = [4, 6];
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
	}, 1500);

})();
