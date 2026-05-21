import {EditorView, KeyBinding} from '@codemirror/view';

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

export const docBlockKeymap: KeyBinding[] = [
	{key: 'Enter', run: expandDocBlock},
];
