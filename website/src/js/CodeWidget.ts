/**
 * Building blocks of the interactive code examples in blog posts
 * (IdentifierExplorer.ts, TypeInferenceDemo.ts): a row of choices, a frame
 * of PHP code highlighted by Prism at build time, and the error messages
 * shown under its lines.
 *
 * Styled with Tailwind classes only (app.pcss lists this file in @source).
 */

export interface CodeError {
	line: number;
	message: string;
	identifier: string;
}

const CHIP_BASE = 'cursor-pointer rounded-full px-3 py-1 text-[13px] leading-5 transition-colors focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600';
const CHIP_ACTIVE = 'bg-gray-900 text-white shadow-sm';
const CHIP_INACTIVE = 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:text-gray-900';

export function html<K extends keyof HTMLElementTagNameMap>(name: K, className: string, parent?: Element, text?: string): HTMLElementTagNameMap[K] {
	const element = document.createElement(name);
	element.className = className;
	if (text !== undefined) {
		element.textContent = text;
	}
	parent?.appendChild(element);

	return element;
}

/** A radio group of chips; the arrow keys move the selection. `mono` for code, like identifiers. */
export function renderChips(group: HTMLElement, values: string[], current: string, onSelect: (value: string) => void, mono = true): void {
	const focused = group.contains(document.activeElement);
	// the chips are swapped in all at once: a row missing some of them for
	// a moment would be shorter, and a page scrolled to the bottom would jump
	const chips = document.createDocumentFragment();
	let activeChip: HTMLButtonElement | null = null;
	for (const value of values) {
		const active = value === current;
		const chip = html('button', `${CHIP_BASE} ${mono ? 'font-mono' : 'font-medium'} ${active ? CHIP_ACTIVE : CHIP_INACTIVE}`, undefined, value);
		chips.appendChild(chip);
		chip.type = 'button';
		chip.setAttribute('role', 'radio');
		chip.setAttribute('aria-checked', active ? 'true' : 'false');
		chip.tabIndex = active ? 0 : -1;
		chip.addEventListener('click', () => onSelect(value));
		chip.addEventListener('keydown', (event) => {
			const step = {ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1}[event.key];
			if (step === undefined) {
				return;
			}
			event.preventDefault();
			const index = (values.indexOf(current) + step + values.length) % values.length;
			onSelect(values[index]);
		});
		if (active) {
			activeChip = chip;
		}
	}
	group.replaceChildren(chips);
	if (focused) {
		activeChip?.focus({preventScroll: true});
	}
}

/**
 * The lines of a Prism-highlighted code block, as HTML. A token spanning
 * several lines (a multi-line comment) is closed at the end of each line and
 * opened again on the next one, so every line stays highlighted on its own.
 */
export function codeLines(codeHtml: string): string[] {
	const template = document.createElement('template');
	template.innerHTML = codeHtml.replace(/\n$/, '');
	const lines: string[] = [];
	let line = '';
	const open: Element[] = [];
	const openTags = (): string => open.map((element) => element.outerHTML.slice(0, element.outerHTML.indexOf('>') + 1)).join('');
	const closeTags = (): string => open.map((element) => `</${element.tagName.toLowerCase()}>`).reverse().join('');
	const walk = (node: Node): void => {
		if (node.nodeType === Node.TEXT_NODE) {
			const pieces = (node.textContent ?? '').split('\n');
			pieces.forEach((piece, index) => {
				if (index > 0) {
					lines.push(line + closeTags());
					line = openTags();
				}
				const text = document.createElement('span');
				text.textContent = piece;
				line += text.innerHTML;
			});

			return;
		}
		if (!(node instanceof Element)) {
			return;
		}
		const tag = node.outerHTML.slice(0, node.outerHTML.indexOf('>') + 1);
		line += tag;
		open.push(node);
		node.childNodes.forEach(walk);
		open.pop();
		line += `</${node.tagName.toLowerCase()}>`;
	};
	template.content.childNodes.forEach(walk);
	lines.push(line);

	return lines;
}

/** The height the element would have without a min-height: its content, padding, borders and scrollbar. */
export function contentHeight(element: HTMLElement): number {
	const style = getComputedStyle(element);
	const chrome = element.offsetHeight - element.clientHeight + parseFloat(style.paddingTop) + parseFloat(style.paddingBottom);
	const top = element.getBoundingClientRect().top + element.clientTop + parseFloat(style.paddingTop);
	let bottom = top;
	for (const child of Array.from(element.children)) {
		bottom = Math.max(bottom, child.getBoundingClientRect().bottom);
	}
	if (element.children.length === 0 && element.textContent !== '') {
		// text directly in the element (a paragraph): measured on a copy taken out of the
		// layout, the element itself keeps its reserved height
		const copy = element.cloneNode(true) as HTMLElement;
		copy.style.cssText = `position: absolute; visibility: hidden; min-height: 0; width: ${element.getBoundingClientRect().width}px`;
		element.after(copy);
		const height = copy.getBoundingClientRect().height;
		copy.remove();

		return height;
	}

	return bottom - top + chrome;
}

/**
 * A frame of code lines that scrolls sideways when a line is too long, with
 * a fade on the right edge while a line continues past it. Rows that are not
 * code (error messages) stay in view while the code scrolls.
 */
export class CodeFrame {
	readonly element: HTMLElement;
	private readonly fade: HTMLElement;
	private readonly resizeObserver: ResizeObserver;
	private stickyRows: HTMLElement[] = [];

	constructor(parent: HTMLElement, className = '') {
		const frame = html('div', `relative ${className}`, parent);
		this.element = html('div', 'overflow-x-auto rounded-xl border border-gray-200 bg-white py-3 text-[13px] leading-6 shadow-sm sm:text-sm', frame);
		this.element.setAttribute('aria-live', 'polite');
		this.fade = html('div', 'pointer-events-none absolute inset-y-px right-px w-10 rounded-r-xl bg-gradient-to-l from-white to-transparent transition-opacity', frame);
		this.element.addEventListener('scroll', () => this.updateFade(), {passive: true});
		this.resizeObserver = new ResizeObserver(() => this.fit());
		this.resizeObserver.observe(this.element);
	}

	/** An empty block to put the rows in, styled by the Prism theme. */
	createCode(): HTMLElement {
		const pre = html('pre', 'language-php m-0! min-w-max border-0! bg-transparent! p-0! font-semibold');

		return html('code', 'language-php block', pre);
	}

	/** Replaces what the frame shows with the rows in `code` (from createCode()). */
	show(code: HTMLElement, keepScroll = false): void {
		const scrollLeft = this.element.scrollLeft;
		this.element.replaceChildren(code.parentElement ?? code);
		this.element.scrollLeft = keepScroll ? scrollLeft : 0;
		this.stickyRows = Array.from(code.querySelectorAll<HTMLElement>('[data-sticky]'));
		this.fit();
	}

	/**
	 * A row under a line of code that does not scroll sideways with it: as
	 * wide as the visible part of the frame, its text wraps instead.
	 */
	stickyRow(code: HTMLElement, gutter: string, className: string): HTMLElement {
		const row = html('div', `sticky left-0 py-1.5 pr-3 pl-3 font-sans font-normal whitespace-normal sm:pr-4 sm:pl-[calc(var(--gutter)+2rem)] ${className}`, code);
		row.dataset.sticky = '';
		row.style.setProperty('--gutter', gutter);

		return row;
	}

	/** The error reported on the line above, with a link to the identifier's documentation. */
	errorRow(code: HTMLElement, gutter: string, error: CodeError): HTMLElement {
		const row = this.stickyRow(code, gutter, 'bg-red-50');
		const box = html('div', 'grid grid-cols-[auto_1fr] items-baseline gap-x-2 gap-y-0.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-[13px] leading-5 text-gray-900 shadow-sm sm:grid-cols-[auto_1fr_auto] sm:gap-x-3', row);
		html('span', 'text-[10px] text-red-600', box, '●').setAttribute('aria-hidden', 'true');
		html('span', 'min-w-0', box, error.message);
		const identifier = html('a', 'col-start-2 font-mono text-xs text-red-700 no-underline! hover:underline! sm:col-start-3', box, error.identifier);
		identifier.href = `/error-identifiers/${error.identifier}`;
		identifier.target = '_blank';
		identifier.rel = 'noopener';

		return row;
	}

	private fit(): void {
		const width = this.element.clientWidth;
		for (const row of this.stickyRows) {
			row.style.width = `${width}px`;
		}
		this.updateFade();
	}

	private updateFade(): void {
		const frame = this.element;
		const hidden = frame.scrollWidth - frame.clientWidth - frame.scrollLeft > 1;
		this.fade.style.opacity = hidden ? '1' : '0';
	}
}

/**
 * Keeps an element the same height whatever the widget shows, so the page
 * does not jump: `renderAll` renders every state in turn - in one go, before
 * the browser paints, so none of them flashes - and the element gets the
 * height of the tallest one. The reserved height stays in place while it is
 * measured: the page must not get shorter even for a moment, or a page
 * scrolled to the bottom would jump. Measured again when the width changes
 * (text wraps differently) and once the web fonts are loaded.
 */
export function reserveHeights(widget: HTMLElement, elements: HTMLElement[], renderAll: (measure: () => void) => void): void {
	const reserve = (): void => {
		const heights = elements.map(() => 0);
		renderAll(() => {
			elements.forEach((element, index) => {
				heights[index] = Math.max(heights[index], contentHeight(element));
			});
		});
		elements.forEach((element, index) => {
			element.style.minHeight = `${heights[index]}px`;
		});
	};
	reserve();
	let width = widget.clientWidth;
	new ResizeObserver(() => {
		if (widget.clientWidth !== width) {
			width = widget.clientWidth;
			reserve();
		}
	}).observe(widget);
	void document.fonts.ready.then(reserve);
}
