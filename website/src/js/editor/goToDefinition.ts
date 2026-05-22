import {Decoration, DecorationSet, EditorView, ViewPlugin} from '@codemirror/view';
import {EditorState, Extension, StateEffect, StateField} from '@codemirror/state';
import {phpantomLsp, PHP_URI} from '../phpantom/lspClient';

// Cmd/Ctrl-click go-to-definition for symbols declared in the playground file
// itself (classes, methods, properties, functions). Holding the modifier and
// hovering shows an underline + pointer over navigable symbols; clicking jumps
// to the declaration and briefly flashes its line. Symbols from phpstorm-stubs,
// PHP builtins or the shipped PHPStan stubs are deliberately not navigable —
// PHPantom resolves their definition to a different (or no) URI, and we only
// act when it points back into the edited document.

interface LspPosition { line: number; character: number }
interface LspLocation { uri: string; range: {start: LspPosition; end: LspPosition} }
interface DefinitionParams { textDocument: {uri: string}; position: LspPosition }

// CodeMirror abstracts the platform modifier for *keymaps* (`Mod-` = Cmd on
// macOS, Ctrl elsewhere) but not for mouse events, so we mirror its own check.
const isMac = typeof navigator !== 'undefined' && /Mac|iP(hone|ad|od)/.test(navigator.platform);
function modHeld(event: MouseEvent): boolean {
	return isMac ? event.metaKey : event.ctrlKey;
}

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

// Ask PHPantom for the definition at `offset`; return the in-document target
// offset, or null if there's none or it lives outside the edited file.
async function resolveInFileDefinition(state: EditorState, offset: number): Promise<number | null> {
	// Flush pending edits to the worker so the definition is resolved against
	// the current document (mirrors what serverCompletion does).
	phpantomLsp.sync();
	let result: LspLocation[] | LspLocation | null;
	try {
		result = await phpantomLsp.request<DefinitionParams, LspLocation[] | LspLocation | null>(
			'textDocument/definition',
			{textDocument: {uri: PHP_URI}, position: toLspPosition(state, offset)},
		);
	} catch {
		return null;
	}
	const location = Array.isArray(result) ? result[0] : result;
	if (!location || location.uri !== PHP_URI) {
		return null;
	}
	return fromLspPosition(state, location.range.start);
}

const linkMark = Decoration.mark({class: 'cm-goto-def-link'});
const flashLine = Decoration.line({class: 'cm-goto-def-flash'});

const setLink = StateEffect.define<{from: number; to: number} | null>();
const setFlash = StateEffect.define<number | null>();

const linkField = StateField.define<DecorationSet>({
	create: () => Decoration.none,
	update(deco, tr) {
		deco = deco.map(tr.changes);
		for (const effect of tr.effects) {
			if (effect.is(setLink)) {
				deco = effect.value
					? Decoration.set([linkMark.range(effect.value.from, effect.value.to)])
					: Decoration.none;
			}
		}
		return deco;
	},
	provide: f => EditorView.decorations.from(f),
});

const flashField = StateField.define<DecorationSet>({
	create: () => Decoration.none,
	update(deco, tr) {
		deco = deco.map(tr.changes);
		for (const effect of tr.effects) {
			if (effect.is(setFlash)) {
				deco = effect.value == null
					? Decoration.none
					: Decoration.set([flashLine.range(tr.state.doc.lineAt(effect.value).from)]);
			}
		}
		return deco;
	},
	provide: f => EditorView.decorations.from(f),
});

const goToDefinitionPlugin = ViewPlugin.fromClass(class {
	private link: {from: number; to: number} | null = null;
	private requestSeq = 0;
	private flashTimer = -1;

	constructor(private readonly view: EditorView) {
		this.onKeyUp = this.onKeyUp.bind(this);
		window.addEventListener('keyup', this.onKeyUp);
	}

	destroy(): void {
		window.removeEventListener('keyup', this.onKeyUp);
		if (this.flashTimer >= 0) {
			clearTimeout(this.flashTimer);
		}
	}

	private onKeyUp(): void {
		this.clearLink();
	}

	clearLink(): void {
		if (this.link !== null) {
			this.link = null;
			this.view.dispatch({effects: setLink.of(null)});
		}
	}

	onMouseMove(event: MouseEvent): void {
		if (!modHeld(event)) {
			this.clearLink();
			return;
		}
		const offset = this.view.posAtCoords({x: event.clientX, y: event.clientY});
		const word = offset == null ? null : this.view.state.wordAt(offset);
		if (offset == null || word == null) {
			this.clearLink();
			return;
		}
		if (this.link !== null && this.link.from === word.from && this.link.to === word.to) {
			return; // already showing this token
		}
		const seq = ++this.requestSeq;
		void resolveInFileDefinition(this.view.state, offset).then(target => {
			if (seq !== this.requestSeq) {
				return; // a newer hover superseded this request
			}
			if (target == null) {
				this.clearLink();
				return;
			}
			this.link = {from: word.from, to: word.to};
			this.view.dispatch({effects: setLink.of(this.link)});
		});
	}

	onMouseDown(event: MouseEvent): boolean {
		if (!modHeld(event)) {
			return false;
		}
		const offset = this.view.posAtCoords({x: event.clientX, y: event.clientY});
		if (offset == null || this.view.state.wordAt(offset) == null) {
			return false;
		}
		// Take over the modifier-click (CodeMirror would otherwise add a cursor).
		event.preventDefault();
		void resolveInFileDefinition(this.view.state, offset).then(target => {
			if (target != null) {
				this.jumpTo(target);
			}
		});
		return true;
	}

	private jumpTo(offset: number): void {
		this.clearLink();
		this.view.dispatch({
			selection: {anchor: offset},
			effects: [EditorView.scrollIntoView(offset, {y: 'center'}), setFlash.of(offset)],
		});
		this.view.focus();
		if (this.flashTimer >= 0) {
			clearTimeout(this.flashTimer);
		}
		this.flashTimer = window.setTimeout(() => {
			this.view.dispatch({effects: setFlash.of(null)});
			this.flashTimer = -1;
		}, 1200);
	}
}, {
	eventHandlers: {
		mousemove(event) { this.onMouseMove(event); },
		mousedown(event) { return this.onMouseDown(event); },
		mouseleave() { this.clearLink(); },
	},
});

export const goToDefinition: Extension = [linkField, flashField, goToDefinitionPlugin];
