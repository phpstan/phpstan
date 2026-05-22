import {Decoration, DecorationSet, EditorView, ViewPlugin, ViewUpdate, keymap} from '@codemirror/view';
import {EditorSelection, EditorState, Extension, Prec, StateEffect, StateField, Transaction} from '@codemirror/state';
import {phpantomLsp, PHP_URI} from '../phpantom/lspClient';

// Two PHPantom-backed editor features over the shared LSP client:
//
//  - Occurrence highlight: whenever the cursor sits inside a symbol, all of its
//    occurrences are faintly highlighted (textDocument/documentHighlight). This
//    is name-based for class members, so a same-named method on a different
//    type may also light up — acceptable for a read-only highlight.
//
//  - Inline rename (Ctrl-R): IntelliJ-style. All occurrences become editable at
//    once via multiple selections, so typing renames them together — no dialog.
//    Only symbols *declared in this file* can be renamed (PHP builtins/stubs are
//    skipped), and the ranges come from textDocument/rename, which is type-aware
//    (renaming A::run never touches B::run). While renaming, edits and the caret
//    are confined to the identifier; Enter confirms, Esc reverts, and clicking
//    away ends the session — so the editor never lingers in multi-cursor mode.

interface LspPosition { line: number; character: number }
interface LspRange { start: LspPosition; end: LspPosition }
interface LspLocation { uri: string; range: LspRange }
interface DocumentHighlight { range: LspRange }
interface TextEdit { range: LspRange; newText: string }
interface WorkspaceEdit { changes?: {[uri: string]: TextEdit[]} }
interface PositionParams { textDocument: {uri: string}; position: LspPosition }

function toLspPosition(state: EditorState, offset: number): LspPosition {
	const line = state.doc.lineAt(offset);
	return {line: line.number - 1, character: offset - line.from};
}

function fromLspPosition(state: EditorState, pos: LspPosition): number {
	if (pos.line >= state.doc.lines) {
		return state.doc.length;
	}
	const line = state.doc.line(pos.line + 1);
	return Math.min(line.from + pos.character, line.to);
}

async function request<R>(method: string, params: PositionParams | (PositionParams & {newName: string})): Promise<R | null> {
	// Flush pending edits so the request resolves against the current document.
	phpantomLsp.sync();
	try {
		return await phpantomLsp.request<typeof params, R | null>(method, params);
	} catch {
		return null;
	}
}

// True only when the symbol's declaration is in the edited document (so we don't
// offer to rename PHP builtins / stub symbols).
async function isLocalSymbol(state: EditorState, offset: number): Promise<boolean> {
	const result = await request<LspLocation[] | LspLocation>('textDocument/definition',
		{textDocument: {uri: PHP_URI}, position: toLspPosition(state, offset)});
	const location = Array.isArray(result) ? result[0] : result;
	return !!location && location.uri === PHP_URI;
}

// ---- Occurrence highlight ---------------------------------------------------

const occurrenceMark = Decoration.mark({class: 'cm-occurrence'});

const setOccurrences = StateEffect.define<DecorationSet>();
const occurrenceField = StateField.define<DecorationSet>({
	create: () => Decoration.none,
	update(deco, tr) {
		deco = deco.map(tr.changes);
		for (const effect of tr.effects) {
			if (effect.is(setOccurrences)) {
				deco = effect.value;
			}
		}
		return deco;
	},
	provide: f => EditorView.decorations.from(f),
});

const occurrencePlugin = ViewPlugin.fromClass(class {
	private seq = 0;
	private timer = -1;

	constructor(view: EditorView) {
		this.schedule(view);
	}

	update(update: ViewUpdate): void {
		if (update.selectionSet || update.docChanged) {
			this.schedule(update.view);
		}
	}

	destroy(): void {
		if (this.timer >= 0) {
			clearTimeout(this.timer);
		}
	}

	private schedule(view: EditorView): void {
		if (this.timer >= 0) {
			clearTimeout(this.timer);
		}
		this.timer = window.setTimeout(() => {
			this.timer = -1;
			void this.run(view);
		}, 150);
	}

	private async run(view: EditorView): Promise<void> {
		// No occurrence highlight while inline-renaming (the selections are there).
		if (view.state.field(renameField) !== null) {
			return;
		}
		const offset = view.state.selection.main.head;
		const seq = ++this.seq;
		const highlights = await request<DocumentHighlight[]>('textDocument/documentHighlight',
			{textDocument: {uri: PHP_URI}, position: toLspPosition(view.state, offset)});
		if (seq !== this.seq || view.state.field(renameField) !== null) {
			return; // superseded, or a rename started meanwhile
		}
		const ranges = (highlights ?? [])
			.map(h => ({from: fromLspPosition(view.state, h.range.start), to: fromLspPosition(view.state, h.range.end)}))
			.filter(r => r.to > r.from)
			.sort((a, b) => a.from - b.from)
			.map(r => occurrenceMark.range(r.from, r.to));
		view.dispatch({effects: setOccurrences.of(Decoration.set(ranges))});
	}
});

// ---- Inline rename session --------------------------------------------------

interface RenameSession {
	// The editable identifier ranges (without the variable's leading `$`), kept
	// in sync with edits so they grow/shrink as you type.
	ranges: readonly {from: number; to: number}[];
	// The original identifier text, used to revert on Esc.
	original: string;
}

const startRename = StateEffect.define<RenameSession>();
const stopRename = StateEffect.define<null>();

const renameField = StateField.define<RenameSession | null>({
	create: () => null,
	update(session, tr) {
		for (const effect of tr.effects) {
			if (effect.is(startRename)) {
				return effect.value;
			}
			if (effect.is(stopRename)) {
				return null;
			}
		}
		if (session === null) {
			return null;
		}
		if (tr.docChanged) {
			return {
				original: session.original,
				ranges: session.ranges.map(r => ({
					from: tr.changes.mapPos(r.from, -1),
					to: tr.changes.mapPos(r.to, 1),
				})),
			};
		}
		return session;
	},
});

function rangeFor(session: RenameSession, pos: number): {from: number; to: number} {
	let best = session.ranges[0];
	let bestDistance = Infinity;
	for (const range of session.ranges) {
		if (pos >= range.from && pos <= range.to) {
			return range;
		}
		const distance = pos < range.from ? range.from - pos : pos - range.to;
		if (distance < bestDistance) {
			bestDistance = distance;
			best = range;
		}
	}
	return best;
}

// Block edits that fall outside the renamed identifiers — e.g. backspacing into
// the `$` of a variable, or deleting the character after the name.
const renameChangeGuard = EditorState.changeFilter.of((tr: Transaction) => {
	const session = tr.startState.field(renameField);
	if (session === null || !tr.docChanged) {
		return true;
	}
	let allowed = true;
	tr.changes.iterChangedRanges((fromA, toA) => {
		if (!session.ranges.some(r => fromA >= r.from && toA <= r.to)) {
			allowed = false;
		}
	});
	return allowed;
});

// Keep the caret inside the identifiers (arrow keys can't escape `aaa` in
// `$aaa`); a pointer click ends the rename instead.
const renameSelectionGuard = EditorState.transactionFilter.of((tr: Transaction) => {
	const session = tr.startState.field(renameField);
	if (session === null || tr.docChanged || tr.selection === undefined) {
		return tr;
	}
	if (tr.isUserEvent('select.pointer')) {
		return [tr, {effects: stopRename.of(null)}];
	}
	const ranges = tr.selection.ranges.map(sel => {
		const range = rangeFor(session, sel.head);
		const head = Math.min(Math.max(sel.head, range.from), range.to);
		const anchor = Math.min(Math.max(sel.anchor, range.from), range.to);
		return EditorSelection.range(anchor, head);
	});
	return [tr, {selection: EditorSelection.create(ranges, tr.selection.mainIndex)}];
});

function endRename(view: EditorView, revert: boolean): boolean {
	const session = view.state.field(renameField);
	if (session === null) {
		return false; // not renaming — let the key do its normal thing
	}
	if (revert) {
		const changes = session.ranges.map(r => ({from: r.from, to: r.to, insert: session.original}));
		view.dispatch({
			changes,
			selection: EditorSelection.cursor(session.ranges[0].from + session.original.length),
			effects: stopRename.of(null),
		});
	} else {
		view.dispatch({
			selection: EditorSelection.cursor(view.state.selection.main.head),
			effects: stopRename.of(null),
		});
	}
	view.focus();
	return true;
}

const renameKeymap = Prec.highest(keymap.of([
	{key: 'Enter', run: view => endRename(view, false)},
	{key: 'Escape', run: view => endRename(view, true)},
]));

/// Occurrence highlighting + the inline-rename machinery. Also enables multiple
/// selections, which inline rename relies on (all occurrences edited at once).
export const occurrenceHighlight: Extension = [
	EditorState.allowMultipleSelections.of(true),
	renameField,
	renameChangeGuard,
	renameSelectionGuard,
	renameKeymap,
	occurrenceField,
	occurrencePlugin,
];

/// Ctrl-R: start IntelliJ-style inline rename of the symbol under the cursor.
export function inlineRename(view: EditorView): boolean {
	void startInlineRename(view);
	return true; // always consume the key (never fall through to browser reload)
}

async function startInlineRename(view: EditorView): Promise<void> {
	const offset = view.state.selection.main.head;
	if (!await isLocalSymbol(view.state, offset)) {
		return; // not declared in this file — nothing to rename
	}
	const edit = await request<WorkspaceEdit>('textDocument/rename',
		{textDocument: {uri: PHP_URI}, position: toLspPosition(view.state, offset), newName: 'PhpantomRenamePlaceholder'});
	const edits = edit?.changes?.[PHP_URI];
	if (!edits || edits.length === 0) {
		return;
	}
	const ranges = edits.map(e => {
		let from = fromLspPosition(view.state, e.range.start);
		const to = fromLspPosition(view.state, e.range.end);
		// Keep the leading `$` of a variable; only the name is editable.
		if (view.state.doc.sliceString(from, from + 1) === '$') {
			from += 1;
		}
		return {from, to};
	}).sort((a, b) => a.from - b.from);

	let main = ranges.findIndex(r => offset >= r.from && offset <= r.to);
	if (main < 0) {
		main = 0;
	}
	const original = view.state.doc.sliceString(ranges[main].from, ranges[main].to);
	view.dispatch({
		selection: EditorSelection.create(ranges.map(r => EditorSelection.range(r.from, r.to)), main),
		effects: [startRename.of({ranges, original}), setOccurrences.of(Decoration.none)],
	});
	view.focus();
}
