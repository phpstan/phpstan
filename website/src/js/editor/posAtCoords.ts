import {EditorView} from '@codemirror/view';

// CodeMirror's posAtCoords can throw a TypeError when the line under the
// pointer has no line tile in the rendered DOM. Treat that like "no position".
export function safePosAtCoords(view: EditorView, event: MouseEvent, precise = true): number | null {
	try {
		const coords = {x: event.clientX, y: event.clientY};
		return precise ? view.posAtCoords(coords) : view.posAtCoords(coords, false);
	} catch {
		return null;
	}
}
