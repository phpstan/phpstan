/**
 * Interactive demonstration of how PHPStan infers types from what comes
 * later in the code: the reader comments lines of an example in and out and
 * sees the types inferred on other lines - a generic object where it is
 * created, a closure's signature - and the errors reported.
 *
 * The results are not computed in the browser - PHPStan was run on every
 * combination of commented-out lines beforehand and the results are in the
 * JSON config. Wrap it and one code block per scenario in
 * `<div class="type-inference-demo">`, `data-hint` is the text above the code
 * (a scenario's own `hint` replaces it):
 *
 *     <div class="type-inference-demo" data-hint="Switch the lines on and off.">
 *
 *     <script type="application/json">
 *     [{
 *         "id": "first-usage",
 *         "label": "Passed to a function",
 *         "toggles": [10, 11],
 *         "defaults": [true, false],
 *         "probes": [{"line": 9, "variable": "ints", "showName": false}],
 *         "results": [
 *             {"types": ["Collection<*NEVER*>"], "errors": []},
 *             ...
 *         ]
 *     }]
 *     </script>
 *
 *     <div data-scenario="first-usage">
 *
 *     ```php
 *     ...
 *     ```
 *
 *     </div>
 *
 *     </div>
 *
 * - `toggles`: the lines that can be commented out, `defaults` whether each
 *   of them is in the code at first.
 * - `probes`: the variables whose type is shown at the end of a line, with
 *   their name unless the line assigns them.
 * - `results`: indexed by a bitmask of the toggled lines that are in the
 *   code - bit 0 is the first of `toggles`. `types` are the inferred types
 *   at the `probes`, null for a probe on a commented-out line; `errors` the
 *   errors PHPStan reports.
 *
 * Without JavaScript and in RSS readers, the scenarios are shown one after
 * another with all their lines. Built from the parts in CodeWidget.ts.
 */

import {CodeError, CodeFrame, codeLines, html, renderChips, reserveHeights} from './CodeWidget';

interface Probe {
	line: number;
	variable: string;
	showName: boolean;
}

interface Result {
	types: (string | null)[];
	errors: CodeError[];
}

interface Scenario {
	id: string;
	label: string;
	hint?: string;
	toggles: number[];
	defaults: boolean[];
	probes: Probe[];
	results: Result[];
	codeHtml: string;
	active: boolean[];
}

function parseScenarios(root: Element): Scenario[] {
	const config = root.querySelector('script[type="application/json"]');
	if (config === null) {
		return [];
	}
	const scenarios: Scenario[] = [];
	for (const scenario of JSON.parse(config.textContent ?? '[]') as Omit<Scenario, 'codeHtml' | 'active'>[]) {
		const code = root.querySelector(`[data-scenario="${scenario.id}"] pre code`);
		if (code === null) {
			continue;
		}
		scenarios.push({...scenario, codeHtml: code.innerHTML, active: [...scenario.defaults]});
	}

	return scenarios;
}

const INLAY = 'inline-block rounded-md px-1.5 font-mono text-xs leading-4 font-semibold ring-1 transition-colors duration-500';
const INLAY_IDLE = 'bg-blue-50 text-blue-800 ring-blue-200';
const INLAY_CHANGED = 'bg-blue-600 text-white ring-blue-600';

class TypeInferenceDemo {
	private current: Scenario;
	private readonly chips: HTMLElement;
	private readonly hint: HTMLElement;
	private readonly defaultHint: string;
	private readonly codeFrame: CodeFrame;
	/** the types shown before the last change, to highlight the ones it changed */
	private shownTypes: (string | null)[] = [];

	constructor(root: HTMLElement, private readonly scenarios: Scenario[]) {
		this.current = scenarios[0];
		for (const element of Array.from(root.children) as HTMLElement[]) {
			element.hidden = true;
		}

		const widget = html('div', 'not-prose my-8', root);
		this.chips = html('div', 'flex flex-wrap gap-1.5', widget);
		this.chips.setAttribute('role', 'radiogroup');
		this.chips.setAttribute('aria-label', 'Example');
		// a single example needs no choice
		this.chips.hidden = scenarios.length === 1;
		this.hint = html('p', `${scenarios.length === 1 ? '' : 'mt-3'} text-sm text-gray-500`, widget);
		this.defaultHint = root.dataset.hint ?? 'Switch the lines on and off.';

		this.codeFrame = new CodeFrame(widget, 'mt-4');

		this.render(false);
		reserveHeights(widget, [this.hint, this.codeFrame.element], (measure) => {
			const current = this.current;
			for (const scenario of this.scenarios) {
				this.current = scenario;
				const active = scenario.active;
				for (let mask = 0; mask < 2 ** scenario.toggles.length; mask++) {
					scenario.active = scenario.toggles.map((_, bit) => (mask >> bit & 1) === 1);
					this.render(false);
					measure();
				}
				scenario.active = active;
			}
			this.current = current;
			this.render(false);
		});
	}

	private result(): Result {
		const mask = this.current.active.reduce((sum, active, bit) => sum + (active ? 2 ** bit : 0), 0);

		return this.current.results[mask];
	}

	private toggle(index: number): void {
		this.current.active[index] = !this.current.active[index];
		this.render(true);
	}

	private render(highlightChanges: boolean): void {
		this.hint.textContent = this.current.hint ?? this.defaultHint;
		renderChips(this.chips, this.scenarios.map((scenario) => scenario.label), this.current.label, (label) => {
			const scenario = this.scenarios.find((candidate) => candidate.label === label);
			if (scenario !== undefined && scenario !== this.current) {
				this.current = scenario;
				this.render(false);
			}
		}, false);

		const result = this.result();
		const lines = codeLines(this.current.codeHtml);
		const gutter = `${String(lines.length).length}ch`;
		const code = this.codeFrame.createCode();
		// the lines are clicked to switch them, which would select the text
		code.classList.add('select-none');
		const focusedToggle = (document.activeElement as HTMLElement | null)?.dataset?.toggle;
		const changed: HTMLElement[] = [];
		const placements: {inline: HTMLElement; belowRow: HTMLElement}[] = [];

		lines.forEach((lineHtml, index) => {
			const number = index + 1;
			const toggleIndex = this.current.toggles.indexOf(number);
			const toggleable = toggleIndex !== -1;
			const commentedOut = toggleable && !this.current.active[toggleIndex];
			const errors = commentedOut ? [] : result.errors.filter((error) => error.line === number);
			const reported = errors.length > 0;

			const row = html('div', `group flex items-center pr-4 ${reported ? 'bg-red-50 shadow-[inset_3px_0_0] shadow-red-500' : ''} ${toggleable ? 'cursor-pointer hover:bg-gray-50' : ''}`, code);
			const switchCell = html('span', 'flex w-11 shrink-0 justify-center', row);
			if (toggleable) {
				const button = html('button', `relative h-4 w-7 shrink-0 cursor-pointer rounded-full transition-colors focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 ${commentedOut ? 'bg-gray-300 group-hover:bg-gray-400' : 'bg-blue-600'}`, switchCell);
				button.type = 'button';
				button.setAttribute('role', 'switch');
				button.setAttribute('aria-checked', commentedOut ? 'false' : 'true');
				button.setAttribute('aria-label', `Line ${number}`);
				button.dataset.toggle = String(number);
				html('span', `absolute top-0.5 left-0.5 size-3 rounded-full bg-white shadow-sm transition-transform ${commentedOut ? '' : 'translate-x-3'}`, button);
				row.addEventListener('click', () => this.toggle(toggleIndex));
				if (focusedToggle === String(number)) {
					requestAnimationFrame(() => button.focus({preventScroll: true}));
				}
			}
			html('span', `mr-4 inline-block shrink-0 select-none text-right tabular-nums ${reported ? 'text-red-500' : 'text-gray-400'}`, row, String(number)).style.width = gutter;

			const content = html('span', 'whitespace-pre', row);
			if (commentedOut) {
				// shown as a comment, without the highlighting of the code
				const indentation = /^\s*/.exec(lineHtml)?.[0] ?? '';
				const text = new DOMParser().parseFromString(lineHtml, 'text/html').body.textContent ?? '';
				content.className = 'whitespace-pre font-normal text-gray-400 italic';
				content.textContent = `${indentation}// ${text.trimStart()}`;
			} else {
				content.innerHTML = lineHtml === '' ? ' ' : lineHtml;
			}

			this.current.probes.forEach((probe, probeIndex) => {
				const type = result.types[probeIndex];
				if (probe.line !== number || type === null || commentedOut) {
					return;
				}
				const previous = this.shownTypes[probeIndex];
				const highlight = highlightChanges && previous !== undefined && previous !== null && previous !== type;
				// next to the code on wider screens; under it on phones, where the
				// line would push it out of view
				const inline = html('span', `${INLAY} ${highlight ? INLAY_CHANGED : INLAY_IDLE} ml-3 max-sm:hidden`, row, probe.showName ? `$${probe.variable}: ${type}` : type);
				inline.title = `Type of $${probe.variable}`;
				const belowRow = this.codeFrame.stickyRow(code, gutter, 'py-0.5! pl-[calc(2.75rem+var(--gutter)+1rem)]! sm:hidden');
				const below = html('span', `${INLAY} ${highlight ? INLAY_CHANGED : INLAY_IDLE}`, belowRow, `$${probe.variable}: ${type}`);
				placements.push({inline, belowRow});
				if (highlight) {
					changed.push(inline, below);
				}
			});

			for (const error of errors) {
				this.codeFrame.errorRow(code, gutter, error);
			}
		});

		this.codeFrame.show(code, true);
		this.placeTypes(placements);
		this.shownTypes = [...result.types];
		if (changed.length > 0) {
			// fade the highlight of the changed types away
			requestAnimationFrame(() => requestAnimationFrame(() => {
				for (const element of changed) {
					element.className = element.className.replace(INLAY_CHANGED, INLAY_IDLE);
				}
			}));
		}
	}

	/**
	 * A type that would not fit next to its line moves under it, as on phones,
	 * so it is never hidden past the right edge of the frame.
	 */
	private placeTypes(placements: {inline: HTMLElement; belowRow: HTMLElement}[]): void {
		const frame = this.codeFrame.element;
		const visibleRight = frame.getBoundingClientRect().left + frame.clientWidth - 16;
		for (const {inline, belowRow} of placements) {
			if (inline.offsetParent === null) {
				continue; // on phones, where the type is always under the line
			}
			if (inline.getBoundingClientRect().right + frame.scrollLeft > visibleRight) {
				inline.style.display = 'none';
				belowRow.classList.remove('sm:hidden');
			}
		}
	}
}

export function initTypeInferenceDemos(): void {
	for (const root of Array.from(document.querySelectorAll<HTMLElement>('.type-inference-demo'))) {
		const scenarios = parseScenarios(root);
		if (scenarios.length > 0) {
			new TypeInferenceDemo(root, scenarios);
		}
	}
}
