/**
 * Interactive line chart rendered from a Markdown table.
 *
 * Wrap the table in `<div class="line-chart">` together with a JSON config.
 * Keep blank lines around the `<script>` block and the table so Markdown
 * still parses the table:
 *
 *     <div class="line-chart">
 *
 *     <script type="application/json">
 *     {
 *         "x": "Version",
 *         "y": {"suffix": " s", "decimals": 1},
 *         "tooltip": {"title": "PHPStan {Version}", "subtitle": "{Released}", "description": "{Note}"},
 *         "series": [
 *             {"id": "off", "column": "Turbo off (s)", "label": "Turbo off", "color": "text-blue-600", "labels": ["first", "last"]},
 *             {"id": "on", "column": "Turbo on (s)", "label": "Turbo on", "color": "text-orange-600"}
 *         ],
 *         "comparisons": [{"from": "off", "to": "on", "text": "Turbo is {percent}% faster"}]
 *     }
 *     </script>
 *
 *     | Version | Released | Turbo off (s) | Turbo on (s) | Note |
 *     |---|---|--:|--:|---|
 *     | 2.1.33 | December 5th 2025 | 60.51 |  | Last release before... |
 *
 *     </div>
 *
 * Config reference:
 *
 * - `x`: column with the X axis labels. Defaults to the first column.
 * - `xDetail`: column with a second, smaller line under each X axis label.
 *   Labels that do not fit next to each other are rotated, the two lines
 *   joined into one.
 * - `y.prefix`, `y.suffix`: added around every value, e.g. `" s"` or `"%"`.
 * - `y.decimals`: decimals of values in labels and the tooltip (default 1).
 * - `y.min`, `y.max`: fixed axis range. By default the axis spans the lowest
 *   to the highest value, extended by `y.padding` (share of the range, default 0.08).
 * - `series[].column`: column with the values. An empty cell leaves the point
 *   out, so a line can start later than the others.
 * - `series[].label`: name in the legend and the tooltip (defaults to the column).
 * - `series[].id`: name for `comparisons` (defaults to the column).
 * - `series[].color`: Tailwind text color class of the line, e.g. "text-blue-600".
 *   The full class name must be written out so Tailwind finds it in the page
 *   and generates it. Blue and orange are the most distinguishable pair, also
 *   for colorblind readers; prefer shades 600 and darker, lighter ones lack
 *   contrast against the white background.
 * - `series[].labels`: which points get their value written next to them,
 *   any of "first" and "last" (default ["last"]).
 * - `tooltip.title`, `tooltip.subtitle`, `tooltip.description`: templates,
 *   `{Column name}` is replaced with the cell of the hovered row.
 * - `comparisons[]`: extra tooltip line comparing two series at the hovered
 *   point; `{percent}` is how much less `to` is than `from` in percent,
 *   `{ratio}` is `from / to`.
 * - `description`: accessible description of the chart for screen readers.
 * - `height`: height of the plot. A number is a fixed height in pixels; an object
 *   `{"ratio": 0.4, "min": 220, "max": 320}` follows the width - `ratio` of it,
 *   kept between `min` and `max` pixels. Any of the three can be left out, the
 *   defaults are 0.52, 280 and 400.
 *
 * The table stays in the page for RSS readers and screen readers, visually
 * hidden. The build (.eleventy.js) runs this same code headlessly to put
 * a static SVG placeholder of the chart into the HTML, so the chart is visible
 * before this script loads and without JS; the script then replaces the
 * placeholder with the interactive chart.
 *
 * Styled with Tailwind classes only (app.pcss lists this file in @source). In
 * dark mode the chart is inverted together with the rest of the page.
 */

interface SeriesConfig {
	column: string;
	id?: string;
	label?: string;
	color?: string;
	labels?: ('first' | 'last')[];
}

interface ComparisonConfig {
	from: string;
	to: string;
	text: string;
}

interface ChartConfig {
	x?: string;
	xDetail?: string;
	y?: {
		prefix?: string;
		suffix?: string;
		decimals?: number;
		min?: number;
		max?: number;
		padding?: number;
	};
	series: SeriesConfig[];
	tooltip?: {
		title?: string;
		subtitle?: string;
		description?: string;
	};
	comparisons?: ComparisonConfig[];
	description?: string;
	height?: number | {
		ratio?: number;
		min?: number;
		max?: number;
	};
}

interface Series {
	id: string;
	label: string;
	colorClasses: string[];
	labels: ('first' | 'last')[];
	values: (number | null)[];
}

type Row = Record<string, string>;

const SVG_NS = 'http://www.w3.org/2000/svg';

// Short line in the series color, used in the legend and the tooltip
const KEY_CLASSES = 'inline-block h-0.75 w-4 shrink-0 rounded-full bg-current';

// Values written next to the points
const VALUE_CLASSES = 'fill-gray-900 font-semibold';

// The second line of an X axis label
const DETAIL_CLASSES = 'fill-gray-400 text-[11px]';

// Used for series without a configured color, in this order
const DEFAULT_COLORS = [
	'text-blue-600',
	'text-orange-600',
	'text-emerald-600',
	'text-amber-600',
	'text-pink-600',
	'text-violet-700',
	'text-red-600',
	'text-green-700',
];

function parseTable(table: HTMLTableElement): {columns: string[]; rows: Row[]} {
	const text = (cell: HTMLTableCellElement): string => (cell.textContent ?? '').trim();
	const columns = Array.from(table.tHead?.rows[0]?.cells ?? []).map(text);
	const rows = Array.from(table.tBodies[0]?.rows ?? []).map((row) => {
		const values: Row = {};
		Array.from(row.cells).forEach((cell, i) => {
			if (i < columns.length) {
				values[columns[i]] = text(cell);
			}
		});

		return values;
	});

	return {columns, rows};
}

function fillTemplate(template: string, row: Row): string {
	return template.replace(/\{([^}]+)\}/g, (match, column: string) => row[column] ?? match);
}

function svg<K extends keyof SVGElementTagNameMap>(name: K, attributes: Record<string, string | number>, parent?: Element): SVGElementTagNameMap[K] {
	const element = document.createElementNS(SVG_NS, name);
	for (const [key, value] of Object.entries(attributes)) {
		element.setAttribute(key, String(value));
	}
	if (parent !== undefined) {
		parent.appendChild(element);
	}

	return element;
}

function html<K extends keyof HTMLElementTagNameMap>(name: K, className: string, parent?: Element, text?: string): HTMLElementTagNameMap[K] {
	const element = document.createElement(name);
	element.className = className;
	if (text !== undefined) {
		element.textContent = text;
	}
	if (parent !== undefined) {
		parent.appendChild(element);
	}

	return element;
}

function colorize<T extends HTMLElement | SVGElement>(element: T, series: Series): T {
	element.classList.add(...series.colorClasses);

	return element;
}

function niceStep(range: number, targetTicks: number): number {
	const rough = range / targetTicks;
	const magnitude = Math.pow(10, Math.floor(Math.log10(rough)));
	for (const multiplier of [1, 2, 5, 10]) {
		if (multiplier * magnitude >= rough) {
			return multiplier * magnitude;
		}
	}

	return 10 * magnitude;
}

export class LineChart {

	private readonly xLabels: string[];

	private readonly xDetails: string[] | null;

	private readonly rows: Row[];

	private readonly series: Series[];

	private readonly chart: HTMLElement;

	private readonly tooltip: HTMLElement;

	private selectedIndex: number | null = null;

	private lastWidth = 0;

	constructor(root: HTMLElement, table: HTMLTableElement, private readonly config: ChartConfig) {
		const {columns, rows} = parseTable(table);
		const column = (name: string): string => {
			if (!columns.includes(name)) {
				throw new Error(`Line chart: column "${name}" not found in the table, available columns: ${columns.join(', ')}`);
			}

			return name;
		};

		const xColumn = column(config.x ?? columns[0]);
		this.rows = rows;
		this.xLabels = rows.map((row) => row[xColumn]);
		const xDetailColumn = config.xDetail !== undefined ? column(config.xDetail) : null;
		this.xDetails = xDetailColumn !== null ? rows.map((row) => row[xDetailColumn]) : null;
		this.series = config.series.map((seriesConfig, i) => {
			const valueColumn = column(seriesConfig.column);
			const color = seriesConfig.color ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length];

			return {
				id: seriesConfig.id ?? valueColumn,
				label: seriesConfig.label ?? valueColumn,
				colorClasses: color.split(/\s+/).filter((c) => c !== ''),
				labels: seriesConfig.labels ?? ['last'],
				values: rows.map((row) => {
					const value = parseFloat(row[valueColumn].replace(/[^0-9.-]/g, ''));
					return isNaN(value) ? null : value;
				}),
			};
		});

		for (const placeholder of Array.from(root.querySelectorAll('[data-line-chart-placeholder]'))) {
			placeholder.remove();
		}

		const figure = html('div', 'mb-8 text-gray-900');
		figure.setAttribute('data-line-chart-figure', '');
		root.insertBefore(figure, root.firstChild);

		if (this.series.length > 1) {
			const legend = html('div', 'mb-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500', figure);
			for (const series of this.series) {
				const item = html('span', 'inline-flex items-center gap-2', legend);
				colorize(html('span', KEY_CLASSES, item), series);
				item.appendChild(document.createTextNode(series.label));
			}
		}

		this.chart = html('div', 'relative w-full', figure);
		this.tooltip = html('div', 'invisible pointer-events-none absolute w-max max-w-60 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-snug shadow-lg', this.chart);
		this.tooltip.setAttribute('aria-hidden', 'true');
		this.tooltip.setAttribute('data-line-chart-tooltip', '');

		table.classList.add('sr-only');

		this.render();
		new ResizeObserver(() => {
			if (this.chart.clientWidth !== this.lastWidth) {
				this.render();
			}
		}).observe(this.chart);
	}

	private format(value: number, decimals?: number): string {
		const y = this.config.y ?? {};

		return `${y.prefix ?? ''}${value.toFixed(decimals ?? y.decimals ?? 1)}${y.suffix ?? ''}`;
	}

	private render(): void {
		const xLabels = this.xLabels;
		const count = xLabels.length;
		const width = this.chart.clientWidth;
		this.lastWidth = width;
		if (width === 0 || count < 2) {
			return;
		}

		const slot = width / count;
		const heightConfig = this.config.height ?? {};
		const height = typeof heightConfig === 'number'
			? heightConfig
			: Math.round(Math.min(heightConfig.max ?? 400, Math.max(heightConfig.min ?? 280, width * (heightConfig.ratio ?? 0.52))));

		const yConfig = this.config.y ?? {};
		const values = this.series.flatMap((s) => s.values).filter((v): v is number => v !== null);
		const min = Math.min(...values);
		const max = Math.max(...values);
		const padding = (max - min) * (yConfig.padding ?? 0.08);
		const low = yConfig.min ?? min - padding;
		const high = yConfig.max ?? max + padding;

		const step = niceStep(high - low, 7);
		const tickDecimals = Math.max(0, -Math.floor(Math.log10(step)));
		const ticks: number[] = [];
		for (let tick = Math.ceil(low / step) * step; tick <= high + step / 1000; tick += step) {
			ticks.push(tick);
		}

		for (const old of Array.from(this.chart.querySelectorAll('svg'))) {
			old.remove();
		}

		const description = this.config.description
			?? `Line chart of ${this.series.map((s) => s.label).join(' and ')} from ${xLabels[0]} to ${xLabels[count - 1]}.`;
		const root = svg('svg', {
			width,
			height,
			viewBox: `0 0 ${width} ${height}`,
			class: 'block touch-pan-y select-none rounded text-xs tabular-nums focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-400',
			role: 'img',
			tabindex: 0,
			'aria-label': `${description} Use arrow keys to step through the points.`,
		});
		this.chart.insertBefore(root, this.tooltip);

		// Margins fit the labels outside the plot: Y ticks on the left,
		// values after the last points on the right, and the halves of the
		// centered first and last X labels
		const measure = (text: string, className = ''): number => {
			const element = svg('text', {class: className}, root);
			element.textContent = text;
			const textWidth = element.getBBox().width;
			element.remove();

			return textWidth;
		};
		const endLabelWidth = Math.max(0, ...this.series
			.filter((s) => s.labels.includes('last') && s.values[count - 1] !== null)
			.map((s) => measure(this.format(s.values[count - 1] ?? 0), VALUE_CLASSES) + 14));
		// An X label with its detail line under it takes the wider of the two lines; labels that
		// do not fit their slot side by side are rotated by 45 degrees, the lines joined into one
		const xDetails = this.xDetails;
		const labelWidths = xLabels.map((xLabel, i) => Math.max(
			measure(xLabel),
			xDetails !== null ? measure(xDetails[i], DETAIL_CLASSES) : 0,
		));
		// neighbouring labels may share the slot between them, a wide one next to narrow ones fits
		const rotateLabels = labelWidths.some((labelWidth, i) => i > 0 && (labelWidths[i - 1] + labelWidth) / 2 + 6 > slot);
		const rotatedTexts = xLabels.map((xLabel, i) => xDetails !== null && xDetails[i] !== '' ? `${xLabel}, ${xDetails[i]}` : xLabel);
		// rotated, a label reaches its width * sin(45) left of and below its point
		const rotated = Math.SQRT1_2;
		const edgeLabel = (i: number): number => rotateLabels ? 0 : labelWidths[i] / 2 + 2;
		const margin = {
			top: 24,
			right: Math.ceil(Math.max(12, endLabelWidth, edgeLabel(count - 1))),
			bottom: rotateLabels
				? Math.ceil(Math.max(...rotatedTexts.map((text) => measure(text))) * rotated + 24)
				: (xDetails !== null ? 46 : 32),
			left: Math.ceil(Math.max(
				...ticks.map((tick) => measure(this.format(tick, tickDecimals)) + 10),
				edgeLabel(0),
				// the glyphs reach further left than the baseline by about their height
				rotateLabels ? measure(rotatedTexts[0]) * rotated + 12 : 0,
			)),
		};
		const plotWidth = width - margin.left - margin.right;
		const plotHeight = height - margin.top - margin.bottom;

		const x = (i: number): number => margin.left + plotWidth * i / (count - 1);
		const y = (v: number): number => margin.top + plotHeight * (high - v) / (high - low);

		// Gridlines and Y ticks
		const grid = svg('g', {class: 'fill-gray-500 stroke-gray-200'}, root);
		for (const tick of ticks) {
			svg('line', {x1: margin.left, x2: width - margin.right, y1: y(tick), y2: y(tick), 'shape-rendering': 'crispEdges'}, grid);
			const label = svg('text', {x: margin.left - 8, y: y(tick), 'text-anchor': 'end', 'dominant-baseline': 'middle', stroke: 'none'}, grid);
			label.textContent = this.format(tick, tickDecimals);
		}

		// X labels
		const xAxis = svg('g', {class: 'fill-gray-500'}, root);
		xLabels.forEach((xLabel, i) => {
			const labelY = margin.top + plotHeight + 20;
			if (rotateLabels) {
				const label = svg('text', {x: x(i), y: labelY - 6, 'text-anchor': 'end', transform: `rotate(-45 ${x(i)} ${labelY - 6})`}, xAxis);
				label.textContent = rotatedTexts[i];
				return;
			}

			const label = svg('text', {x: x(i), y: labelY, 'text-anchor': 'middle'}, xAxis);
			label.textContent = xLabel;
			if (xDetails === null || xDetails[i] === '') {
				return;
			}

			const detail = svg('text', {x: x(i), y: labelY + 14, 'text-anchor': 'middle', class: DETAIL_CLASSES}, xAxis);
			detail.textContent = xDetails[i];
		});

		// Crosshair sits under the lines
		const crosshair = svg('line', {class: 'stroke-gray-400', 'shape-rendering': 'crispEdges', y1: margin.top, y2: margin.top + plotHeight, x1: 0, x2: 0, visibility: 'hidden'}, root);

		// Lines, markers and direct labels
		const markers: SVGCircleElement[][] = [];
		for (const series of this.series) {
			const points = series.values
				.map((value, i) => ({i, value}))
				.filter((p): p is {i: number; value: number} => p.value !== null);
			const group = colorize(svg('g', {}, root), series);
			svg('path', {
				d: points.map((p, j) => `${j === 0 ? 'M' : 'L'}${x(p.i)},${y(p.value)}`).join(''),
				fill: 'none',
				stroke: 'currentColor',
				'stroke-width': 2,
				'stroke-linejoin': 'round',
				'stroke-linecap': 'round',
			}, group);

			const seriesMarkers: SVGCircleElement[] = [];
			for (const p of points) {
				seriesMarkers[p.i] = svg('circle', {class: 'stroke-white stroke-2', fill: 'currentColor', cx: x(p.i), cy: y(p.value), r: 4}, group);
			}
			markers.push(seriesMarkers);

			if (points.length === 0) {
				continue;
			}

			const first = points[0];
			const last = points[points.length - 1];
			if (series.labels.includes('first')) {
				// Placed on the side the line leaves, above a falling line and below a rising one
				const rising = points.length > 1 && points[1].value > first.value;
				const label = svg('text', {class: VALUE_CLASSES, x: x(first.i) + 10, y: y(first.value) + (rising ? 16 : -4)}, root);
				label.textContent = this.format(first.value);
			}
			if (series.labels.includes('last')) {
				const label = svg('text', {class: VALUE_CLASSES, x: x(last.i) + 10, y: y(last.value), 'dominant-baseline': 'middle'}, root);
				label.textContent = this.format(last.value);
			}
		}

		const select = (index: number | null): void => {
			this.selectedIndex = index;
			markers.forEach((seriesMarkers) => seriesMarkers.forEach((marker, i) => {
				marker.setAttribute('r', i === index ? '6' : '4');
			}));
			if (index === null) {
				crosshair.setAttribute('visibility', 'hidden');
				this.tooltip.classList.add('invisible');
				return;
			}

			crosshair.setAttribute('x1', String(x(index)));
			crosshair.setAttribute('x2', String(x(index)));
			crosshair.setAttribute('visibility', 'visible');
			this.showTooltip(index, x(index), margin.top, width);
		};

		const indexFromPointer = (event: PointerEvent): number => {
			const box = root.getBoundingClientRect();
			const position = (event.clientX - box.left - margin.left) / plotWidth * (count - 1);

			return Math.max(0, Math.min(count - 1, Math.round(position)));
		};

		root.addEventListener('pointermove', (event) => select(indexFromPointer(event)));
		root.addEventListener('pointerdown', (event) => select(indexFromPointer(event)));
		root.addEventListener('pointerleave', (event) => {
			if (event.pointerType === 'mouse') {
				select(null);
			}
		});
		root.addEventListener('focus', () => select(this.selectedIndex ?? count - 1));
		root.addEventListener('blur', () => select(null));
		root.addEventListener('keydown', (event) => {
			const current = this.selectedIndex ?? count - 1;
			if (event.key === 'ArrowLeft') {
				select(Math.max(0, current - 1));
			} else if (event.key === 'ArrowRight') {
				select(Math.min(count - 1, current + 1));
			} else if (event.key === 'Escape') {
				select(null);
			} else {
				return;
			}
			event.preventDefault();
		});

		if (this.selectedIndex !== null) {
			select(this.selectedIndex);
		}
	}

	private showTooltip(index: number, anchorX: number, top: number, width: number): void {
		const tooltip = this.tooltip;
		const row = this.rows[index];
		const templates = this.config.tooltip ?? {};
		tooltip.replaceChildren();

		const head = html('div', 'flex flex-col', tooltip);
		html('strong', '', head, templates.title !== undefined ? fillTemplate(templates.title, row) : this.xLabels[index]);
		const subtitle = templates.subtitle !== undefined ? fillTemplate(templates.subtitle, row) : '';
		if (subtitle !== '') {
			html('span', 'text-xs text-gray-500', head, subtitle);
		}
		const description = templates.description !== undefined ? fillTemplate(templates.description, row) : '';
		if (description !== '') {
			html('div', 'mt-1 text-xs text-gray-500', tooltip, description);
		}

		const rows = html('div', 'mt-2', tooltip);
		for (const series of this.series) {
			const value = series.values[index];
			if (value === null) {
				continue;
			}
			const seriesRow = html('div', 'flex items-center gap-2 tabular-nums', rows);
			colorize(html('span', KEY_CLASSES, seriesRow), series);
			html('strong', '', seriesRow, this.format(value));
			html('span', 'text-gray-500', seriesRow, series.label);
		}

		for (const comparison of this.config.comparisons ?? []) {
			const from = this.series.find((s) => s.id === comparison.from)?.values[index] ?? null;
			const to = this.series.find((s) => s.id === comparison.to)?.values[index] ?? null;
			if (from === null || to === null || from === 0 || to === 0) {
				continue;
			}
			html('div', 'mt-2 border-t border-gray-200 pt-2 font-semibold', tooltip, comparison.text
				.replace('{percent}', String(Math.round((from - to) / from * 100)))
				.replace('{ratio}', (from / to).toFixed(2)));
		}

		tooltip.classList.remove('invisible');
		const tooltipWidth = tooltip.offsetWidth;
		const gap = 12;
		let left = anchorX + gap;
		if (left + tooltipWidth > width) {
			left = anchorX - gap - tooltipWidth;
		}
		tooltip.style.left = `${Math.max(0, left)}px`;
		tooltip.style.top = `${top}px`;
	}

}

export function initLineCharts(): void {
	for (const root of Array.from(document.querySelectorAll<HTMLElement>('.line-chart'))) {
		const table = root.querySelector('table');
		const configScript = root.querySelector('script[type="application/json"]');
		try {
			if (table === null || configScript === null) {
				throw new Error('Line chart: .line-chart needs a table and a <script type="application/json"> config');
			}

			new LineChart(root, table, JSON.parse(configScript.textContent ?? '') as ChartConfig);
		} catch (e) {
			// report the broken chart, but keep rendering the others
			setTimeout(() => {
				throw e;
			});
		}
	}
}
