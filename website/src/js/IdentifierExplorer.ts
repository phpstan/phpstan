/**
 * Interactive picker of code examples by error identifier, built from Markdown.
 *
 * Wrap the examples in `<div class="identifier-explorer">`, each in its own
 * `<div data-identifier="...">` with a PHP code block and the errors PHPStan
 * reports for it, one list item per error. Keep blank lines around the HTML
 * tags so Markdown still parses what is between them:
 *
 *     <div class="identifier-explorer">
 *
 *     <div data-identifier="variable.unused">
 *
 *     `variable.unused`
 *
 *     ```php
 *     function foo(): void
 *     {
 *         $unused = 1;
 *     }
 *     ```
 *
 *     * Line 3: Variable $unused is never read. `variable.unused`
 *
 *     </div>
 *
 *     </div>
 *
 * The identifiers are split at the dot: the reader picks the first part,
 * then one of the second parts the examples have for it - picking a first
 * part selects its first second part. `data-where` on an
 * example replaces the first part in the choices, to split a first part with
 * many second parts into groups (`data-where="foreach key"`). The examples keep
 * their order - the first part's order is the order in which it first
 * appears. The errors with the picked identifier are shown under the lines
 * they are reported on; other errors listed for the example are left out.
 *
 * Without JavaScript and in RSS readers, all the examples are listed one
 * after another. Built from the parts in CodeWidget.ts.
 */

import {CodeError, CodeFrame, codeLines, html, renderChips, reserveHeights} from './CodeWidget';

interface Example {
	identifier: string;
	first: string;
	second: string;
	codeHtml: string;
	errors: CodeError[];
}

const ERROR_LINE = /^Line (\d+):\s*([\s\S]*?)\s*$/;

function parseExamples(root: Element): Example[] {
	const examples: Example[] = [];
	for (const container of Array.from(root.querySelectorAll<HTMLElement>('[data-identifier]'))) {
		const identifier = container.dataset.identifier ?? '';
		const code = container.querySelector('pre code');
		const dot = identifier.indexOf('.');
		if (code === null || dot === -1) {
			continue;
		}
		const errors: CodeError[] = [];
		for (const item of Array.from(container.querySelectorAll('li'))) {
			const errorIdentifier = item.querySelector('code')?.textContent ?? identifier;
			const clone = item.cloneNode(true) as HTMLElement;
			clone.querySelector('code')?.remove();
			const match = ERROR_LINE.exec(clone.textContent ?? '');
			if (match === null) {
				continue;
			}
			errors.push({line: Number(match[1]), message: match[2], identifier: errorIdentifier});
		}
		examples.push({
			identifier,
			first: container.dataset.where ?? identifier.slice(0, dot),
			second: identifier.slice(dot + 1),
			codeHtml: code.innerHTML,
			errors,
		});
	}

	return examples;
}

class IdentifierExplorer {
	private readonly firsts: string[];
	private current: Example;
	private readonly firstGroup: HTMLElement;
	private readonly secondGroup: HTMLElement;
	private readonly codeFrame: CodeFrame;

	constructor(root: HTMLElement, private readonly examples: Example[]) {
		this.firsts = [...new Set(examples.map((example) => example.first))];
		this.current = examples[0];
		for (const element of Array.from(root.children) as HTMLElement[]) {
			element.hidden = true;
		}

		const widget = html('div', 'not-prose my-8', root);

		const pickers = html('div', 'grid gap-x-4 gap-y-2.5 sm:grid-cols-[max-content_1fr] sm:items-baseline', widget);
		html('div', 'text-xs font-medium uppercase tracking-wide text-gray-500', pickers, 'Where');
		this.firstGroup = html('div', 'flex flex-wrap gap-1.5', pickers);
		this.firstGroup.setAttribute('role', 'radiogroup');
		this.firstGroup.setAttribute('aria-label', 'First part of the identifier');
		html('div', 'mt-1 text-xs font-medium uppercase tracking-wide text-gray-500 sm:mt-0', pickers, 'What');
		this.secondGroup = html('div', 'flex flex-wrap content-start items-start gap-1.5', pickers);
		this.secondGroup.setAttribute('role', 'radiogroup');
		this.secondGroup.setAttribute('aria-label', 'Second part of the identifier');

		this.codeFrame = new CodeFrame(widget, 'mt-6');

		this.render();
		reserveHeights(widget, [this.secondGroup, this.codeFrame.element], (measure) => {
			const current = this.current;
			for (const example of this.examples) {
				this.current = example;
				this.render();
				measure();
			}
			this.current = current;
			this.render();
		});
	}

	private select(example: Example): void {
		this.current = example;
		this.render();
	}

	private selectFirst(first: string): void {
		if (first === this.current.first) {
			return;
		}
		const example = this.examples.find((candidate) => candidate.first === first);
		if (example !== undefined) {
			this.select(example);
		}
	}

	private render(): void {
		const {first, second} = this.current;
		renderChips(this.firstGroup, this.firsts, first, (value) => this.selectFirst(value));
		const seconds = this.examples.filter((example) => example.first === first);
		renderChips(this.secondGroup, seconds.map((example) => example.second), second, (value) => {
			const example = seconds.find((candidate) => candidate.second === value);
			if (example !== undefined) {
				this.select(example);
			}
		});

		this.renderCode();
	}

	private renderCode(): void {
		const lines = codeLines(this.current.codeHtml);
		const gutter = `${String(lines.length).length}ch`;
		const code = this.codeFrame.createCode();
		lines.forEach((lineHtml, index) => {
			const number = index + 1;
			const errors = this.current.errors.filter((error) => error.line === number && error.identifier === this.current.identifier);
			const reported = errors.length > 0;
			const row = html('div', `flex px-4 ${reported ? 'bg-red-50 shadow-[inset_3px_0_0] shadow-red-500' : ''}`, code);
			html('span', `mr-4 inline-block shrink-0 select-none text-right tabular-nums ${reported ? 'text-red-500' : 'text-gray-400'}`, row, String(number)).style.width = gutter;
			const content = html('span', 'whitespace-pre', row);
			content.innerHTML = lineHtml === '' ? ' ' : lineHtml;

			for (const error of errors) {
				this.codeFrame.errorRow(code, gutter, error);
			}
		});

		this.codeFrame.show(code);
	}
}

export function initIdentifierExplorers(): void {
	for (const root of Array.from(document.querySelectorAll<HTMLElement>('.identifier-explorer'))) {
		const examples = parseExamples(root);
		if (examples.length > 0) {
			new IdentifierExplorer(root, examples);
		}
	}
}
