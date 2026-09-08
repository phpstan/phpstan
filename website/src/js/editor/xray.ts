import {EditorView, layer, RectangleMarker, LayerMarker, ViewPlugin, ViewUpdate, showTooltip, Tooltip} from '@codemirror/view';
import {ChangeSet, EditorSelection, EditorState, Extension, Facet, StateEffect, StateField, Text} from '@codemirror/state';
import {XRayData} from '../XRayData';

// AST X-Ray: a toggle in the editor's corner that outlines every AST node
// PHPStan sees, and on hover names the node, lists its sub-nodes and shows the
// type PHPStan resolved for it. The data comes from the playground runner (see
// playground-runner/xray.php); this file only renders it.
//
// While it is on, the editor flips to a monochrome negative (black on white
// becomes white on black and vice versa) without syntax highlighting, so the
// structure is what stands out.
//
// Rendering happens in a layer (absolutely positioned boxes above the text,
// like CodeMirror's own selection layer) rather than with mark decorations,
// because marks are split at every overlap boundary and could not draw one
// border around a node that has children.

export interface XRayProp {
	name: string;
	// A child node index, a list of those, or a rendered scalar.
	value: number | string | (number | string)[];
}

export interface XRayNode {
	kind: string;
	from: number;
	to: number;
	parent: number;
	depth: number;
	type: string | null;
	props: XRayProp[];
	// Whether the node gets its own box and can be hovered. Nodes that would
	// only duplicate a box (Name/Identifier, an Arg around its expression, ...)
	// are still in the tree and show up in the popup's path.
	drawn: boolean;
	multiLine: boolean;
	// How many levels of drawn nodes of the same shape (single-line or block)
	// nest inside this one; outer boxes grow outward by this.
	height: number;
}

interface XRayState {
	enabled: boolean;
	busy: boolean;
	nodes: XRayNode[];
	hovered: number;
	// Where the popup for the hovered node is anchored.
	anchor: number;
	// Edits made since the last analysis request went out. The data that
	// answers it refers to the document as it was then, so it is mapped through
	// these on arrival; null before the first request.
	pending: ChangeSet | null;
}

export const setXRayData = StateEffect.define<XRayData | null>();
export const setXRayEnabled = StateEffect.define<boolean>();
export const setXRayBusy = StateEffect.define<boolean>();
// Dispatched right before an analysis request is sent.
export const startXRayRequest = StateEffect.define<null>();
const setHovered = StateEffect.define<{node: number; anchor: number}>();

const xrayConfig = Facet.define<{onToggle: (enabled: boolean) => void}, {onToggle: (enabled: boolean) => void}>({
	combine: values => values.length > 0 ? values[0] : {onToggle: () => undefined},
});

// Not offered on phones (the site's small breakpoint) or on touch-only devices:
// the feature lives on hovering, and the overlay needs room.
export const xrayUnavailable: MediaQueryList | null = typeof window !== 'undefined' && typeof window.matchMedia === 'function'
	? window.matchMedia('(max-width: 640px), (hover: none)')
	: null;

export function isXRayAvailable(): boolean {
	return xrayUnavailable === null || !xrayUnavailable.matches;
}

function isHiddenKind(kind: string): boolean {
	return kind.startsWith('Name') || kind.startsWith('Identifier') || kind === 'VarLikeIdentifier' || kind === 'Stmt\\Nop';
}

function buildNodes(data: XRayData, doc: Text, pending: ChangeSet | null): XRayNode[] {
	const string = (index: number): string => data.strings[index] ?? '';
	const mapFrom = (pos: number): number => Math.min(pending ? pending.mapPos(pos, 1) : pos, doc.length);
	const mapTo = (pos: number): number => Math.min(pending ? pending.mapPos(pos, -1) : pos, doc.length);
	const propValue = (value: number | number[]): XRayProp['value'] => {
		if (Array.isArray(value)) {
			return value.map(v => v >= 0 ? v : string(-v - 1));
		}
		return value >= 0 ? value : string(-value - 1);
	};
	const nodes: XRayNode[] = data.nodes.map(([kind, from, to, parent, type, props]) => {
		const parsedProps: XRayProp[] = [];
		for (let i = 0; i + 1 < props.length; i += 2) {
			parsedProps.push({name: string(props[i] as number), value: propValue(props[i + 1])});
		}
		const mappedFrom = mapFrom(from);
		return {
			kind: string(kind),
			from: mappedFrom,
			to: Math.max(mappedFrom, mapTo(to)),
			parent,
			depth: 0,
			type: type >= 0 ? string(type) : null,
			props: parsedProps,
			drawn: true,
			multiLine: false,
			height: 0,
		};
	});
	for (const node of nodes) {
		node.depth = node.parent >= 0 ? nodes[node.parent].depth + 1 : 0;
		node.drawn = !isHiddenKind(node.kind) && node.to > node.from;
		if (node.kind === 'Expr\\Variable' && node.parent >= 0 && nodes[node.parent].kind === 'Param') {
			node.drawn = false;
		}
	}
	// A node whose range is exactly one of its children's (Arg around its
	// expression, a key-less ArrayItem) would only draw a second box.
	for (const node of nodes) {
		if (!node.drawn || node.parent < 0) {
			continue;
		}
		const parent = nodes[node.parent];
		if (parent.from === node.from && parent.to === node.to) {
			parent.drawn = false;
		}
	}
	layoutNodes(nodes, doc);
	return nodes;
}

function layoutNodes(nodes: XRayNode[], doc: Text): void {
	for (const node of nodes) {
		const last = Math.max(node.from, node.to - 1);
		node.multiLine = doc.lineAt(node.from).number !== doc.lineAt(last).number;
		node.height = 0;
	}
	// Children follow their parents in pre-order, so walking backwards sees a
	// node's whole subtree before the node itself.
	for (let i = nodes.length - 1; i >= 0; i--) {
		const node = nodes[i];
		if (!node.drawn) {
			continue;
		}
		for (let p = node.parent; p >= 0; p = nodes[p].parent) {
			const ancestor = nodes[p];
			if (!ancestor.drawn) {
				continue;
			}
			if (ancestor.multiLine === node.multiLine) {
				ancestor.height = Math.max(ancestor.height, node.height + 1);
			}
			break;
		}
	}
}

export const xrayField = StateField.define<XRayState>({
	create: () => ({enabled: false, busy: false, nodes: [], hovered: -1, anchor: 0, pending: null}),
	update(state, tr) {
		let next = state;
		if (tr.docChanged) {
			// Like a decoration set, the boxes follow edits until fresh data arrives.
			const nodes = state.nodes.map(node => {
				const from = tr.changes.mapPos(node.from, 1);
				return {...node, from, to: Math.max(from, tr.changes.mapPos(node.to, -1))};
			});
			layoutNodes(nodes, tr.state.doc);
			next = {...next, nodes, hovered: -1, pending: state.pending ? state.pending.compose(tr.changes) : null};
		}
		for (const effect of tr.effects) {
			if (effect.is(startXRayRequest)) {
				next = {...next, pending: ChangeSet.empty(tr.state.doc.length)};
			} else if (effect.is(setXRayData)) {
				next = {...next, nodes: effect.value ? buildNodes(effect.value, tr.state.doc, next.pending) : [], hovered: -1};
			} else if (effect.is(setXRayEnabled)) {
				const enabled = effect.value && isXRayAvailable();
				next = {...next, enabled, hovered: enabled ? next.hovered : -1};
			} else if (effect.is(setXRayBusy)) {
				next = {...next, busy: effect.value};
			} else if (effect.is(setHovered)) {
				next = {...next, hovered: effect.value.node, anchor: effect.value.anchor};
			}
		}
		return next;
	},
});

export function isXRayActive(state: EditorState): boolean {
	return state.field(xrayField).enabled;
}

// ---- Boxes -------------------------------------------------------------------

// Every box keeps this much clear of its text; each level of nesting inside
// it pushes its edges out by a further step, which is the band you hover to
// reach the outer node. Edges closer than SNAP snap together so there are no
// one-pixel slivers between boxes.
const PAD_BASE = 3;
const PAD_STEP = 4;
const SNAP = 3;
// Boxes stop this short of the row above and below so neighbours never touch.
const ROW_MARGIN = 2;

interface Box {
	node: number;
	left: number;
	top: number;
	right: number;
	bottom: number;
	block: boolean;
}

// Boxes from the last layout, in layer coordinates, for hit-testing.
const hitBoxes = new WeakMap<EditorView, Box[]>();

// Dash length and period of the box outlines, in pixels.
const DASH = 3;
const DASH_PERIOD = 6;

// A box whose dashed outline is drawn with background gradients (see the
// theme) rather than a CSS border. A border's dashes start at the box's own
// corner, so two boxes sharing an edge at different offsets fill each other's
// gaps and add up to a solid line. Here the pattern is anchored to the layer,
// so every edge at the same x or y is in phase and coinciding edges paint the
// very same pixels.
class BoxMarker implements LayerMarker {

	constructor(
		private readonly className: string,
		private readonly left: number,
		private readonly top: number,
		private readonly width: number,
		private readonly height: number,
	) {}

	eq(other: LayerMarker): boolean {
		return other instanceof BoxMarker && other.className === this.className
			&& other.left === this.left && other.top === this.top && other.width === this.width && other.height === this.height;
	}

	draw(): HTMLElement {
		const elt = document.createElement('div');
		elt.className = this.className;
		this.adjust(elt);
		return elt;
	}

	update(elt: HTMLElement, prev: LayerMarker): boolean {
		if (!(prev instanceof BoxMarker) || prev.className !== this.className) {
			return false;
		}
		this.adjust(elt);
		return true;
	}

	private adjust(elt: HTMLElement): void {
		elt.style.left = this.left + 'px';
		elt.style.top = this.top + 'px';
		elt.style.width = this.width + 'px';
		elt.style.height = this.height + 'px';
		const phase = (value: number): string => -(((value % DASH_PERIOD) + DASH_PERIOD) % DASH_PERIOD) + 'px';
		elt.style.setProperty('--cm-xray-dx', phase(this.left));
		elt.style.setProperty('--cm-xray-dy', phase(this.top));
	}

}

function layerBase(view: EditorView): {left: number; top: number} {
	const rect = view.scrollDOM.getBoundingClientRect();
	return {
		left: rect.left - view.scrollDOM.scrollLeft * view.scaleX,
		top: rect.top - view.scrollDOM.scrollTop * view.scaleY,
	};
}

function boxClass(state: XRayState, index: number): string {
	const node = state.nodes[index];
	let cls = 'cm-xray-box' + (node.multiLine ? ' cm-xray-box-block' : '');
	if (state.hovered < 0) {
		return cls;
	}
	if (state.hovered === index) {
		return cls + ' cm-xray-box-hovered';
	}
	for (let p = state.nodes[state.hovered].parent; p >= 0; p = state.nodes[p].parent) {
		if (p === index) {
			cls += ' cm-xray-box-ancestor';
			break;
		}
	}
	return cls;
}

function layoutBoxes(view: EditorView, state: XRayState): Box[] {
	const boxes: Box[] = [];
	const {from: viewFrom, to: viewTo} = view.viewport;
	const doc = view.state.doc;
	const base = layerBase(view);
	const contentRect = view.contentDOM.getBoundingClientRect();
	const contentLeft = (contentRect.left - base.left) / view.scaleX;
	const contentRight = (contentRect.right - base.left) / view.scaleX;
	const firstVisibleLine = doc.lineAt(viewFrom).number;
	const lastVisibleLine = doc.lineAt(viewTo).number;
	const rowHeight = view.defaultLineHeight;

	state.nodes.forEach((node, index) => {
		if (!node.drawn || node.to < viewFrom || node.from > viewTo) {
			return;
		}
		const pad = PAD_BASE + PAD_STEP * node.height;

		if (!node.multiLine) {
			for (const piece of RectangleMarker.forRange(view, '', EditorSelection.range(node.from, node.to))) {
				if (piece.width === null) {
					continue;
				}
				// forRange measures the glyphs; the box spans the row they sit in.
				const rowTop = piece.top + piece.height / 2 - rowHeight / 2;
				boxes.push({
					node: index,
					left: Math.max(contentLeft, piece.left - pad),
					right: Math.min(contentRight, piece.left + piece.width + pad),
					top: rowTop + ROW_MARGIN,
					bottom: rowTop + rowHeight - ROW_MARGIN,
					block: false,
				});
			}
			return;
		}

		// Multi-line node: one box around everything it spans. Left hugs the
		// least indented of its lines, right the longest one; the lines outside
		// the viewport can't be measured and are ignored.
		const startLine = doc.lineAt(node.from);
		const endLine = doc.lineAt(Math.max(node.from, node.to - 1));
		let left = Infinity;
		let right = -Infinity;
		const fromLine = Math.max(startLine.number, firstVisibleLine);
		const toLine = Math.min(endLine.number, lastVisibleLine);
		for (let n = fromLine; n <= toLine; n++) {
			const line = doc.line(n);
			const indent = /^\s*/.exec(line.text)![0].length;
			const lineStart = n === startLine.number ? node.from : Math.min(line.from + indent, line.to);
			const lineEnd = n === endLine.number ? node.to : line.to;
			const startCoords = view.coordsAtPos(lineStart, 1);
			const endCoords = view.coordsAtPos(lineEnd, -1);
			if (startCoords) {
				left = Math.min(left, (startCoords.left - base.left) / view.scaleX);
			}
			if (endCoords) {
				right = Math.max(right, (endCoords.right - base.left) / view.scaleX);
			}
		}
		if (left === Infinity || right === -Infinity) {
			return;
		}
		const top = view.lineBlockAt(node.from).top + ROW_MARGIN;
		const bottom = view.lineBlockAt(Math.max(node.from, node.to - 1)).bottom - ROW_MARGIN;
		if (bottom <= top) {
			return;
		}
		boxes.push({
			node: index,
			left: Math.max(contentLeft, left - pad),
			right: Math.min(contentRight, right + pad),
			top,
			bottom,
			block: true,
		});
	});

	snapEdges(boxes);
	return boxes;
}

// Vertical edges of boxes on the same row that end up a pixel or two apart
// (a sibling's padding meeting another's) are moved onto each other. Block
// boxes are anchors; only single-line boxes move.
function snapEdges(boxes: Box[]): void {
	const rows = new Map<number, Box[]>();
	for (const box of boxes) {
		if (box.block) {
			continue;
		}
		const key = Math.round(box.top);
		const row = rows.get(key);
		if (row) {
			row.push(box);
		} else {
			rows.set(key, [box]);
		}
	}
	const blocks = boxes.filter(b => b.block);
	for (const row of rows.values()) {
		const rowTop = row[0].top;
		const anchors: number[] = [];
		for (const block of blocks) {
			if (block.top <= rowTop + 0.5 && block.bottom >= row[0].bottom - 0.5) {
				anchors.push(block.left, block.right);
			}
		}
		const edges: {x: number; box: Box; side: 'left' | 'right'}[] = [];
		for (const box of row) {
			edges.push({x: box.left, box, side: 'left'}, {x: box.right, box, side: 'right'});
		}
		for (const edge of edges) {
			for (const anchor of anchors) {
				if (Math.abs(edge.x - anchor) <= SNAP) {
					edge.x = anchor;
				}
			}
		}
		edges.sort((a, b) => a.x - b.x);
		let previous = -Infinity;
		for (const edge of edges) {
			if (edge.x - previous > 0 && edge.x - previous <= SNAP) {
				edge.x = previous;
			}
			previous = edge.x;
			edge.box[edge.side] = edge.x;
		}
	}
}

function buildMarkers(view: EditorView): readonly LayerMarker[] {
	const state = view.state.field(xrayField);
	if (!state.enabled || state.nodes.length === 0) {
		hitBoxes.set(view, []);
		return [];
	}
	const boxes = layoutBoxes(view, state);
	hitBoxes.set(view, boxes);
	const markers: LayerMarker[] = [];
	for (const box of boxes) {
		// Whole pixels keep the 1px dashes crisp and the shared edges identical.
		const left = Math.round(box.left);
		const top = Math.round(box.top);
		const width = Math.round(box.right) - left;
		const height = Math.round(box.bottom) - top;
		if (width <= 0 || height <= 0) {
			continue;
		}
		markers.push(new BoxMarker(boxClass(state, box.node), left, top, width, height));
	}
	return markers;
}

const boxesLayer = layer({
	above: true,
	class: 'cm-xray-layer',
	updateOnDocViewUpdate: false,
	update(update: ViewUpdate) {
		return update.docChanged || update.viewportChanged || update.geometryChanged
			|| update.transactions.some(tr => tr.effects.some(e => e.is(setXRayData) || e.is(setXRayEnabled) || e.is(setHovered)));
	},
	markers: buildMarkers,
});

// ---- Hover ---------------------------------------------------------------------

const hoverPlugin = ViewPlugin.fromClass(class {
	private pending: MouseEvent | null = null;
	private frame = -1;

	constructor(private readonly view: EditorView) {}

	destroy(): void {
		if (this.frame >= 0) {
			cancelAnimationFrame(this.frame);
		}
	}

	onMouseMove(event: MouseEvent): void {
		if (!this.view.state.field(xrayField).enabled) {
			return;
		}
		this.pending = event;
		if (this.frame < 0) {
			this.frame = requestAnimationFrame(() => {
				this.frame = -1;
				if (this.pending) {
					this.resolve(this.pending);
				}
			});
		}
	}

	clear(): void {
		this.pending = null;
		const state = this.view.state.field(xrayField);
		if (state.hovered >= 0) {
			this.view.dispatch({effects: setHovered.of({node: -1, anchor: 0})});
		}
	}

	private resolve(event: MouseEvent): void {
		const state = this.view.state.field(xrayField);
		if (!state.enabled) {
			return;
		}
		const base = layerBase(this.view);
		const x = (event.clientX - base.left) / this.view.scaleX;
		const y = (event.clientY - base.top) / this.view.scaleY;
		// The innermost box under the pointer wins.
		let hit = -1;
		for (const box of hitBoxes.get(this.view) ?? []) {
			if (x >= box.left && x <= box.right && y >= box.top && y <= box.bottom
				&& (hit < 0 || state.nodes[box.node].depth > state.nodes[hit].depth)) {
				hit = box.node;
			}
		}
		if (hit === state.hovered) {
			return;
		}
		if (hit < 0) {
			this.clear();
			return;
		}
		const node = state.nodes[hit];
		const anchor = node.multiLine
			? (this.view.posAtCoords({x: event.clientX, y: event.clientY}, false) ?? node.from)
			: node.from;
		this.view.dispatch({effects: setHovered.of({node: hit, anchor: Math.max(node.from, Math.min(node.to, anchor))})});
	}
}, {
	eventHandlers: {
		mousemove(event) { this.onMouseMove(event); },
		mouseleave() { this.clear(); },
	},
});

// ---- Popup ---------------------------------------------------------------------

const MAX_PATH = 4;

function describeValue(nodes: XRayNode[], value: XRayProp['value']): string {
	if (typeof value === 'number') {
		return nodes[value]?.kind ?? '?';
	}
	if (typeof value === 'string') {
		return value;
	}
	if (value.length === 0) {
		return '[]';
	}
	const kinds = value.map(v => typeof v === 'number' ? (nodes[v]?.kind ?? '?') : v);
	if (kinds.every(k => k === kinds[0])) {
		return kinds[0] + '[' + kinds.length + ']';
	}
	return '[' + kinds.slice(0, 3).join(', ') + (kinds.length > 3 ? ', …' : '') + ']';
}

function renderPopup(state: XRayState): HTMLElement {
	const node = state.nodes[state.hovered];
	const dom = document.createElement('div');
	dom.className = 'cm-xray-popup';

	const path: string[] = [];
	for (let p = node.parent; p >= 0; p = state.nodes[p].parent) {
		path.unshift(state.nodes[p].kind);
	}
	if (path.length > 0) {
		const pathDom = document.createElement('div');
		pathDom.className = 'cm-xray-popup-path';
		const shown = path.length > MAX_PATH ? ['…', ...path.slice(path.length - MAX_PATH)] : path;
		pathDom.textContent = shown.join(' › ') + ' ›';
		dom.appendChild(pathDom);
	}

	const kindDom = document.createElement('div');
	kindDom.className = 'cm-xray-popup-kind';
	kindDom.textContent = node.kind;
	dom.appendChild(kindDom);

	const rows = document.createElement('div');
	rows.className = 'cm-xray-popup-rows';
	const addRow = (label: string, value: string, cls: string): void => {
		const labelDom = document.createElement('span');
		labelDom.className = 'cm-xray-popup-label';
		labelDom.textContent = label;
		const valueDom = document.createElement('span');
		valueDom.className = 'cm-xray-popup-value ' + cls;
		valueDom.textContent = value;
		rows.append(labelDom, valueDom);
	};
	if (node.type !== null) {
		addRow('type', node.type, 'cm-xray-popup-type');
	}
	for (const prop of node.props) {
		addRow(prop.name, describeValue(state.nodes, prop.value), '');
	}
	if (rows.childElementCount > 0) {
		dom.appendChild(rows);
	}

	return dom;
}

const popupField = StateField.define<Tooltip | null>({
	create: () => null,
	update(tooltip, tr) {
		const state = tr.state.field(xrayField);
		if (!state.enabled || state.hovered < 0) {
			return null;
		}
		const changed = tr.docChanged || tr.effects.some(e => e.is(setHovered) || e.is(setXRayData) || e.is(setXRayEnabled));
		if (tooltip !== null && !changed) {
			return tooltip;
		}
		return {
			pos: state.anchor,
			above: true,
			arrow: false,
			create: () => {
				const dom = document.createElement('div');
				dom.className = 'cm-xray-tooltip';
				dom.appendChild(renderPopup(state));
				return {dom, offset: {x: 0, y: 6}};
			},
		};
	},
	provide: f => showTooltip.from(f),
});

// ---- Toggle --------------------------------------------------------------------

const togglePlugin = ViewPlugin.fromClass(class {
	private readonly button: HTMLButtonElement;

	constructor(private readonly view: EditorView) {
		this.button = document.createElement('button');
		this.button.type = 'button';
		this.button.className = 'cm-xray-toggle';
		this.button.title = 'Outline every AST node and show the type PHPStan resolved for it';
		this.button.innerHTML = '<span class="cm-xray-toggle-dot"></span><span class="cm-xray-toggle-label">AST X-Ray</span>';
		this.button.addEventListener('mousedown', event => event.preventDefault());
		this.button.addEventListener('click', () => {
			const enabled = !this.view.state.field(xrayField).enabled;
			this.view.dispatch({effects: setXRayEnabled.of(enabled)});
			this.view.state.facet(xrayConfig).onToggle(enabled);
		});
		view.dom.appendChild(this.button);
		this.onAvailabilityChange = this.onAvailabilityChange.bind(this);
		xrayUnavailable?.addEventListener('change', this.onAvailabilityChange);
		this.sync();
	}

	update(update: ViewUpdate): void {
		if (update.state.field(xrayField) !== update.startState.field(xrayField)) {
			this.sync();
		}
	}

	destroy(): void {
		xrayUnavailable?.removeEventListener('change', this.onAvailabilityChange);
		this.button.remove();
	}

	private onAvailabilityChange(): void {
		if (!isXRayAvailable() && this.view.state.field(xrayField).enabled) {
			this.view.dispatch({effects: setXRayEnabled.of(false)});
		}
		this.sync();
	}

	private sync(): void {
		const state = this.view.state.field(xrayField);
		// The class sets display, so the hidden attribute alone would not win.
		this.button.style.display = isXRayAvailable() ? '' : 'none';
		this.button.classList.toggle('cm-xray-toggle-active', state.enabled);
		this.button.classList.toggle('cm-xray-toggle-busy', state.enabled && state.busy);
		this.button.setAttribute('aria-pressed', state.enabled ? 'true' : 'false');
	}
});

// ---- Theme ---------------------------------------------------------------------

// The editor is inverted in X-ray mode: the light theme goes black, the dark
// theme goes light. cm-xray-light / cm-xray-dark name the theme underneath.
const editorClass = EditorView.editorAttributes.compute([xrayField, EditorView.darkTheme], state => {
	if (!state.field(xrayField).enabled) {
		return {class: ''};
	}
	return {class: 'cm-xray-on ' + (state.facet(EditorView.darkTheme) ? 'cm-xray-dark' : 'cm-xray-light')};
});

const DASH_H = `repeating-linear-gradient(90deg, var(--cm-xray-dash) 0 ${DASH}px, transparent ${DASH}px ${DASH_PERIOD}px)`;
const DASH_V = `repeating-linear-gradient(180deg, var(--cm-xray-dash) 0 ${DASH}px, transparent ${DASH}px ${DASH_PERIOD}px)`;

// No line-height change here on purpose: the editor's own line-height (set in
// codeMirror.ts) leaves room for the boxes, so the code doesn't move when the
// overlay is switched on.
const theme = EditorView.baseTheme({
	// Monochrome: no syntax colours, no bold keywords, no error line tints.
	'&.cm-xray-on .cm-line': {
		backgroundColor: 'transparent !important',
	},
	'&.cm-xray-on .cm-line span': {
		color: 'inherit !important',
		fontWeight: 'normal !important',
		fontStyle: 'normal !important',
	},
	'&, & .cm-content, & .cm-gutters': {
		transition: 'background-color 250ms ease, color 250ms ease',
	},
	'&.cm-xray-light, &.cm-xray-light .cm-content': {
		backgroundColor: '#0b0b0b',
		color: '#ededed',
	},
	'&.cm-xray-light .cm-gutters': {
		backgroundColor: '#0b0b0b',
		color: '#5a5a5a',
		borderRight: '1px solid #222',
	},
	'&.cm-xray-light .cm-cursor, &.cm-xray-light .cm-dropCursor': {
		borderLeftColor: '#fff',
	},
	'&.cm-xray-light.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground, &.cm-xray-light .cm-selectionBackground': {
		backgroundColor: 'rgba(255, 255, 255, 0.22)',
	},
	'&.cm-xray-dark, &.cm-xray-dark .cm-content': {
		backgroundColor: '#f4f4f4',
		color: '#141414',
	},
	'&.cm-xray-dark .cm-gutters': {
		backgroundColor: '#f4f4f4',
		color: '#9a9a9a',
		borderRight: '1px solid #ddd',
	},
	'&.cm-xray-dark .cm-cursor, &.cm-xray-dark .cm-dropCursor': {
		borderLeftColor: '#000',
	},
	'&.cm-xray-dark.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground, &.cm-xray-dark .cm-selectionBackground': {
		backgroundColor: 'rgba(0, 0, 0, 0.16)',
	},

	'.cm-xray-layer': {
		pointerEvents: 'none',
	},
	// Four 1px dashed edges, each a gradient strip anchored to the layer grid
	// via --cm-xray-dx/--cm-xray-dy (set per box by BoxMarker). The strips are
	// one period longer than the box so the shift never leaves a gap.
	'.cm-xray-box': {
		boxSizing: 'border-box',
		backgroundImage: [DASH_H, DASH_H, DASH_V, DASH_V].join(', '),
		backgroundSize: `calc(100% + ${DASH_PERIOD}px) 1px, calc(100% + ${DASH_PERIOD}px) 1px, 1px calc(100% + ${DASH_PERIOD}px), 1px calc(100% + ${DASH_PERIOD}px)`,
		backgroundPosition: 'var(--cm-xray-dx) 0, var(--cm-xray-dx) 100%, 0 var(--cm-xray-dy), 100% var(--cm-xray-dy)',
		backgroundRepeat: 'no-repeat',
	},
	'.cm-xray-box-hovered': {
		backgroundImage: 'none',
		borderWidth: '1px',
		borderStyle: 'solid',
		borderRadius: '3px',
	},
	'.cm-xray-box-block.cm-xray-box-hovered': {
		borderRadius: '5px',
	},
	// Opaque greys on purpose: with alpha, edges shared by several boxes would
	// composite on top of each other and come out brighter.
	'&.cm-xray-light .cm-xray-box': {
		'--cm-xray-dash': '#5c5c5c',
	},
	'&.cm-xray-light .cm-xray-box-ancestor': {
		'--cm-xray-dash': '#b8b8b8',
	},
	'&.cm-xray-light .cm-xray-box-hovered': {
		borderColor: '#fff',
		backgroundColor: 'rgba(74, 222, 128, 0.16)',
	},
	'&.cm-xray-light .cm-xray-box-block.cm-xray-box-hovered': {
		backgroundColor: 'rgba(74, 222, 128, 0.06)',
	},
	'&.cm-xray-dark .cm-xray-box': {
		'--cm-xray-dash': '#ababab',
	},
	'&.cm-xray-dark .cm-xray-box-ancestor': {
		'--cm-xray-dash': '#5a5a5a',
	},
	'&.cm-xray-dark .cm-xray-box-hovered': {
		borderColor: '#000',
		backgroundColor: 'rgba(22, 163, 74, 0.14)',
	},
	'&.cm-xray-dark .cm-xray-box-block.cm-xray-box-hovered': {
		backgroundColor: 'rgba(22, 163, 74, 0.05)',
	},

	'.cm-tooltip.cm-xray-tooltip': {
		border: 'none',
		background: 'transparent',
		zIndex: '30',
	},
	'.cm-xray-popup': {
		maxWidth: '36rem',
		padding: '7px 11px 8px',
		borderRadius: '8px',
		border: '1px solid rgba(255, 255, 255, 0.16)',
		background: '#111',
		color: '#fff',
		fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace',
		fontSize: '12px',
		lineHeight: '1.5',
		boxShadow: '0 6px 24px rgba(0, 0, 0, 0.35)',
		whiteSpace: 'pre-wrap',
		wordBreak: 'break-word',
	},
	'.cm-xray-popup-path': {
		color: 'rgba(255, 255, 255, 0.5)',
		fontSize: '11px',
	},
	'.cm-xray-popup-kind': {
		fontWeight: '600',
		fontSize: '13px',
	},
	'.cm-xray-popup-rows': {
		display: 'grid',
		gridTemplateColumns: 'max-content minmax(0, 1fr)',
		columnGap: '14px',
		marginTop: '4px',
	},
	'.cm-xray-popup-label': {
		color: 'rgba(255, 255, 255, 0.5)',
	},
	'.cm-xray-popup-type': {
		color: '#4ade80',
	},

	'.cm-xray-toggle': {
		position: 'absolute',
		top: '0',
		right: '0',
		zIndex: '20',
		display: 'inline-flex',
		alignItems: 'center',
		gap: '7px',
		padding: '6px 11px 6px 10px',
		// Flush with the editor's corner: only the corner it shares with the
		// editor and the one pointing into the text are rounded.
		borderRadius: '0 5px 0 6px',
		border: '1px solid rgba(255, 255, 255, 0.14)',
		borderTop: 'none',
		borderRight: 'none',
		background: '#111',
		color: '#fff',
		fontFamily: 'inherit',
		fontSize: '11px',
		fontWeight: '600',
		letterSpacing: '0.02em',
		lineHeight: '1',
		cursor: 'pointer',
		userSelect: 'none',
		boxShadow: '0 2px 8px rgba(0, 0, 0, 0.25)',
		transition: 'color 200ms ease, text-shadow 200ms ease',
	},
	'.cm-xray-toggle:hover': {
		background: '#000',
	},
	'.cm-xray-toggle-dot': {
		width: '6px',
		height: '6px',
		borderRadius: '50%',
		background: 'rgba(255, 255, 255, 0.35)',
		transition: 'background 200ms ease, box-shadow 200ms ease',
	},
	'.cm-xray-toggle-active': {
		color: '#4ade80',
		textShadow: '0 0 6px rgba(74, 222, 128, 0.9), 0 0 14px rgba(74, 222, 128, 0.5)',
	},
	'.cm-xray-toggle-active .cm-xray-toggle-dot': {
		background: '#4ade80',
		boxShadow: '0 0 6px rgba(74, 222, 128, 0.9), 0 0 12px rgba(74, 222, 128, 0.6)',
		animation: 'cm-xray-pulse 1.8s ease-in-out infinite',
	},
	'.cm-xray-toggle-busy .cm-xray-toggle-dot': {
		animationDuration: '0.6s',
	},
	'@keyframes cm-xray-pulse': {
		'0%, 100%': {opacity: '1'},
		'50%': {opacity: '0.45'},
	},
});

export function xray(config: {onToggle: (enabled: boolean) => void}): Extension {
	return [
		xrayConfig.of(config),
		xrayField,
		editorClass,
		boxesLayer,
		hoverPlugin,
		popupField,
		togglePlugin,
		theme,
	];
}
