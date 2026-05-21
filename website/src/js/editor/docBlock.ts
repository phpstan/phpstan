import {EditorView, KeyBinding} from '@codemirror/view';
import {EditorState} from '@codemirror/state';
import {syntaxTree} from '@codemirror/language';
import {SyntaxNode} from '@lezer/common';

// True when `pos` sits inside a `/* ... */` block comment, per the PHP syntax tree.
const insideBlockComment = (state: EditorState, pos: number): boolean => {
	for (let node: SyntaxNode | null = syntaxTree(state).resolveInner(pos, 1); node; node = node.parent) {
		if (node.type.name.toLowerCase().includes('comment')) {
			return true;
		}
	}
	return false;
};

// Expand a PHPDoc block when Enter is pressed right after typing `/**`.
//
// Turns this (cursor shown as |):
//     /**|
// into this:
//     /**
//      * |
//      */
// leaving the cursor after the ` * ` on the middle line, like modern PHP IDEs.
const expandDocBlock = (view: EditorView): boolean => {
	const {state} = view;
	const range = state.selection.main;

	// Only handle a single, empty selection (a plain caret).
	if (state.selection.ranges.length !== 1 || !range.empty) {
		return false;
	}

	const line = state.doc.lineAt(range.head);
	const beforeCursor = state.doc.sliceString(line.from, range.head);
	const match = /^(\s*)\/\*\*$/.exec(beforeCursor);
	if (match === null) {
		return false;
	}

	// Don't expand if there's real content after the caret on this line.
	const afterCursor = state.doc.sliceString(range.head, line.to);
	if (afterCursor.trim() !== '') {
		return false;
	}

	const indent = match[1];
	const insert = `\n${indent} * \n${indent} */`;
	// Caret goes after the ` * ` of the middle line:
	// range.head + "\n" (1) + indent + " * " (3).
	const cursor = range.head + 1 + indent.length + 3;

	view.dispatch({
		changes: {from: range.head, to: line.to, insert},
		selection: {anchor: cursor},
		scrollIntoView: true,
		userEvent: 'input.complete',
	});

	return true;
};

// Continue a block comment when Enter is pressed on one of its ` * ` lines.
//
// Pressing Enter here (cursor shown as |):
//     /**
//      * @param int $x|
//      */
// keeps going with an aligned star:
//     /**
//      * @param int $x
//      * |
//      */
// Any text after the caret is carried onto the new line, so splitting a line works too.
const continueBlockComment = (view: EditorView): boolean => {
	const {state} = view;
	const range = state.selection.main;

	if (state.selection.ranges.length !== 1 || !range.empty) {
		return false;
	}

	const line = state.doc.lineAt(range.head);
	const beforeCursor = state.doc.sliceString(line.from, range.head);
	// A continuation line: whitespace then `*`, but not the closing `*/`.
	const match = /^(\s*)\*(?!\/)/.exec(beforeCursor);
	if (match === null) {
		return false;
	}

	// Make sure we're really inside a block comment, not on a line that merely
	// happens to start with `*` (e.g. an expression).
	if (!insideBlockComment(state, line.from + match[1].length)) {
		return false;
	}

	const indent = match[1];
	const insert = `\n${indent}* `;
	// Caret after the inserted "* ": range.head + "\n" (1) + indent + "* " (2).
	const cursor = range.head + 1 + indent.length + 2;

	view.dispatch({
		changes: {from: range.head, insert},
		selection: {anchor: cursor},
		scrollIntoView: true,
		userEvent: 'input',
	});

	return true;
};

export const docBlockKeymap: KeyBinding[] = [
	{key: 'Enter', run: expandDocBlock},
	{key: 'Enter', run: continueBlockComment},
];
