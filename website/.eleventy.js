const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const pluginRss = require("@11ty/eleventy-plugin-rss").default;
const { DateTime } = require("luxon");
const readingTime = require('reading-time');
const { chromium } = require("@playwright/test");
const fs = require("fs");
const crypto = require("crypto");
const util = require("util");
const { exec } = require("child_process");
const { fixTypos } = require('typopo');
const anchor = require('markdown-it-anchor');
const nunjucks = require("nunjucks");

process.setMaxListeners(0);

// Build-time headless browser (Playwright), shared by Mermaid diagram
// rendering and social-image generation. Launched lazily, reused across the
// whole build, and closed when the build (or each watch rebuild) finishes via
// the 'eleventy.after' hook. This replaces two separate, fragile browser
// stacks -- headless-mermaid (an abandoned package bundling an obsolete
// puppeteer whose Chromium download path Google retired) and capture-website
// (a second puppeteer) -- each of which pulled in its own Chromium and was a
// recurring source of CI breakage.
const mermaidScriptPath = require.resolve('mermaid/dist/mermaid.min.js');
let buildBrowserPromise = null;

function getBuildBrowser() {
	if (buildBrowserPromise === null) {
		buildBrowserPromise = chromium.launch({ args: ['--no-sandbox'] });
	}
	return buildBrowserPromise;
}

async function closeBuildBrowser() {
	if (buildBrowserPromise !== null) {
		const browser = await buildBrowserPromise;
		buildBrowserPromise = null;
		await browser.close();
	}
}

// The interactive parts of a post - line charts with their data tables
// (src/js/LineChart.ts), click-through code examples (src/js/IdentifierExplorer.ts,
// src/js/TypeInferenceDemo.ts) - are not read from start to end, so they do not
// count towards the reading time.
function withoutInteractiveBlocks(html) {
	const start = /<div class="(?:line-chart|identifier-explorer|type-inference-demo)"[^>]*>/g;
	let result = '';
	let position = 0;
	let match;
	while ((match = start.exec(html)) !== null) {
		result += html.slice(position, match.index);
		// skip to the </div> closing this one, past the divs nested in it
		const tag = /<div\b|<\/div>/g;
		tag.lastIndex = match.index + match[0].length;
		let depth = 1;
		let found;
		while (depth > 0 && (found = tag.exec(html)) !== null) {
			depth += found[0] === '</div>' ? -1 : 1;
		}
		position = depth === 0 ? tag.lastIndex : html.length;
		start.lastIndex = position;
	}

	return result + html.slice(position);
}

// Render a line chart (src/js/LineChart.ts) with the same code the browser
// runs, at a fixed width, and return its markup as a static placeholder.
// Only the few styles that affect label measurement are needed here, the
// markup carries its Tailwind classes into the page.
async function renderLineChartPlaceholder(script, fontFace, chartHtml, width) {
	const browser = await getBuildBrowser();
	const page = await browser.newPage({ viewport: { width: width + 100, height: 800 } });
	try {
		await page.setContent('<!DOCTYPE html><html><head><style>' + fontFace
			+ ' body { margin: 0; font-family: "Inter Variable", sans-serif; }'
			+ ' .text-xs { font-size: 12px; } .text-\\[11px\\] { font-size: 11px; } .font-semibold { font-weight: 600; }'
			+ '</style></head><body><div style="width: ' + width + 'px">' + chartHtml + '</div></body></html>');
		await page.evaluate(() => document.fonts.load('12px "Inter Variable"'));
		await page.addScriptTag({ type: 'module', content: script + '\ninitLineCharts();\nwindow.lineChartRendered = true;' });
		await page.waitForFunction(() => window.lineChartRendered === true);
		return await page.evaluate(() => {
			const figure = document.querySelector('[data-line-chart-figure]');
			figure.querySelector('[data-line-chart-tooltip]').remove();
			const svg = figure.querySelector('svg');
			svg.removeAttribute('width');
			svg.removeAttribute('height');
			svg.removeAttribute('tabindex');
			svg.removeAttribute('role');
			svg.removeAttribute('aria-label');
			svg.setAttribute('class', 'block h-auto w-full select-none text-xs tabular-nums');
			figure.removeAttribute('data-line-chart-figure');
			return figure.outerHTML;
		});
	} finally {
		await page.close();
	}
}

// The mermaid library is injected from node_modules, so no network is needed.
async function renderMermaid(definition) {
	const browser = await getBuildBrowser();
	const page = await browser.newPage();
	try {
		await page.setContent('<!DOCTYPE html><html><body></body></html>');
		await page.addScriptTag({ path: mermaidScriptPath });
		return await page.evaluate(async (def) => {
			window.mermaid.initialize({ startOnLoad: false });
			const { svg } = await window.mermaid.render('mermaid-diagram', def);
			return svg;
		}, definition);
	} finally {
		await page.close();
	}
}

// Render the social-image HTML string to an 800x418 PNG buffer. The HTML is
// fully self-contained (inline CSS + base64 font and logo), so no network
// access is needed; we still wait for document.fonts so text never renders in
// a fallback face.
async function renderSocialImage(html) {
	const browser = await getBuildBrowser();
	const page = await browser.newPage({ viewport: { width: 800, height: 418 } });
	try {
		await page.setContent(html, { waitUntil: 'load' });
		await page.evaluate(() => document.fonts.ready);
		return await page.screenshot({ fullPage: true });
	} finally {
		await page.close();
	}
}

module.exports = async function (eleventyConfig) {
	const { EleventyRenderPlugin } = await import("@11ty/eleventy");
	eleventyConfig.ignores.add("src/_posts/CLAUDE.md");
	eleventyConfig.addPassthroughCopy('src/images');
	eleventyConfig.addPassthroughCopy('src/images-emails');
	eleventyConfig.addPassthroughCopy('src/images-emails-2');
	eleventyConfig.addPassthroughCopy('src/js');
	eleventyConfig.addPassthroughCopy('src/errorsIdentifiers.json');
	eleventyConfig.addPassthroughCopy('src/app.pcss');
	eleventyConfig.addPassthroughCopy('src/robots.txt');
	eleventyConfig.addPassthroughCopy('src/release.txt');
	eleventyConfig.addPlugin(syntaxHighlight, {
		codeAttributes: {
			"class": function({ language }) {
				return "language-diff-" + language + " diff-highlight";
			}
		},
	});
	eleventyConfig.addPlugin(pluginRss);
	eleventyConfig.addPlugin(EleventyRenderPlugin);
	eleventyConfig.setDataDeepMerge(true);

	const markdownIt = require("markdown-it");
	const options = {
		html: true,
		breaks: false,
		linkify: false,
		typographer:  true,
	};
	const markdownLib = markdownIt(options).disable('code');
	markdownLib.use(anchor, {
		level: 2,
		permalink: anchor.permalink.linkInsideHeader({
			class: 'header-anchor ml-1 text-gray-300 hover:text-black',
			symbol: '#',
			placement: 'after',
		}),
	});

	markdownLib.use(require('markdown-it-footnote'));
	markdownLib.use(require('markdown-it-abbr'));
	markdownLib.use(require('markdown-it-attrs'));

	eleventyConfig.setLibrary("md", markdownLib);

	eleventyConfig.addFilter("trimInputPath", function(value) {
		if (value.startsWith('./')) {
			return value.substring(2);
		}

		return value;
	});

	eleventyConfig.addFilter('trimDotHtml', function (value) {
		if (value.endsWith('.html')) {
			return value.substring(0, value.length - 5);
		}

		return value;
	});

	eleventyConfig.addFilter('htmlDateString', (dateObj) => {
		return DateTime.fromJSDate(dateObj, {zone: 'utc'}).toFormat('DDD');
	});

	eleventyConfig.addFilter('readingTime', (text) => {
		return readingTime(withoutInteractiveBlocks(text)).text;
	});

	eleventyConfig.addFilter("head", (array, n) => {
		if (n < 0) {
			return array.slice(n);
		}

		return array.slice(0, n);
	});

	eleventyConfig.addFilter('fixTypos', (text) => {
		return fixTypos(text, 'en-us');
	});

	eleventyConfig.addShortcode("year", () => {
		return new Date().getFullYear().toString();
	});

	eleventyConfig.addPairedNunjucksShortcode("markdown", (contentt) => {
		return markdownLib.render(contentt);
	});

	eleventyConfig.addFilter("renderMarkdown", (content) => {
		if (!content) return '';
		return markdownLib.render(content);
	});

	eleventyConfig.addFilter("prevNextNav", function (currentUrl, sidebarGroups, sidebarItems) {
		const items = [];
		if (sidebarGroups) {
			for (const group of sidebarGroups) {
				for (const item of group.items) {
					if (item.link && item.link.startsWith('/') && !item.external) {
						items.push(item);
					}
				}
			}
		} else if (sidebarItems) {
			for (const item of sidebarItems) {
				if (item.link && item.link.startsWith('/') && !item.external) {
					items.push(item);
				}
			}
		}

		const idx = items.findIndex(item => currentUrl === item.link);
		return {
			prev: idx > 0 ? items[idx - 1] : null,
			next: idx >= 0 && idx < items.length - 1 ? items[idx + 1] : null,
		};
	});

	const inspect = require("util").inspect;
	eleventyConfig.addFilter("debug", (content) => `<pre>${inspect(content)}</pre>`);

	eleventyConfig.addPairedShortcode('mermaid', async (content) => {
		const svg = await renderMermaid(content);
		const id = crypto.createHash('sha256').update(svg).digest('hex');
		const name = 'tmp/images/mermaid-' + id + '.svg';
		fs.writeFileSync(name, svg);

		return '<img class="mb-8" src="/images/mermaid-' + id + '.svg" />'
	});

	// Close the shared Mermaid browser once the build (or each watch rebuild)
	// finishes, so the process can exit cleanly.
	eleventyConfig.on('eleventy.after', closeBuildBrowser);

	const nunjucksEnv = new nunjucks.Environment(new nunjucks.FileSystemLoader('.'));
	nunjucksEnv.addFilter('fixTypos', (text) => fixTypos(text, 'en-us'));
	// The social-image template is rendered headlessly from an HTML string, so
	// it must be fully self-contained -- no external stylesheet, font, or
	// image. Inline the Inter subsets (Latin scripts cover the headings) and
	// the logo as base64 data URIs. font-display: block so text never renders
	// in a fallback face during capture.
	const socialImageFontFace = ['latin', 'latin-ext'].map((subset) => {
		const file = require.resolve('@fontsource-variable/inter/files/inter-' + subset + '-wght-normal.woff2');
		const base64 = fs.readFileSync(file).toString('base64');
		return "@font-face {\n"
			+ "\tfont-family: 'Inter Variable';\n"
			+ "\tfont-style: normal;\n"
			+ "\tfont-weight: 100 900;\n"
			+ "\tfont-display: block;\n"
			+ "\tsrc: url('data:font/woff2;base64," + base64 + "') format('woff2');\n"
			+ "}";
	}).join("\n");
	const socialImageLogo = 'data:image/png;base64,' + fs.readFileSync('src/images/logo.png').toString('base64');

	// Generated social images are cached by a hash of everything that affects
	// their output: the template, the inlined font and logo, and the per-post
	// title and date. The cache directory is persisted across CI runs, so only
	// posts whose inputs changed are re-rendered.
	const socialImageVersion = crypto.createHash('sha256')
		.update(fs.readFileSync('./src/_includes/social/socialImage.njk'))
		.update(socialImageFontFace)
		.update(socialImageLogo)
		.digest('hex');
	const socialImageCacheDir = '.cache/social';

	eleventyConfig.addAsyncShortcode('socialImages', async function (title) {
		if (process.env.ELEVENTY_RUN_MODE === 'watch') {
			return '<meta name="twitter:image" content="/images/logo-big.png" />'
				+ "\n"
				+ '<meta property="og:image" content="/images/logo-big.png" />';
		}
		const date = DateTime.fromJSDate(this.page.date, {zone: 'utc'}).toFormat('DDD');
		const id = crypto.createHash('sha256')
			.update(socialImageVersion).update('\0').update(title).update('\0').update(date)
			.digest('hex');
		const cachePath = socialImageCacheDir + '/' + id + '.png';

		let image;
		if (fs.existsSync(cachePath)) {
			image = fs.readFileSync(cachePath);
		} else {
			const content = nunjucksEnv.render('./src/_includes/social/socialImage.njk', {
				title: title,
				date: date,
				fontFace: socialImageFontFace,
				logo: socialImageLogo,
			});
			image = await renderSocialImage(content);
			fs.mkdirSync(socialImageCacheDir, { recursive: true });
			fs.writeFileSync(cachePath, image);
		}
		fs.writeFileSync('tmp/images/social-' + this.page.fileSlug + '.png', image);

		return '<meta name="twitter:image" content="/images/social-' + this.page.fileSlug + '.png" />'
			+ "\n"
			+ '<meta property="og:image" content="/images/social-' + this.page.fileSlug + '.png" />';
	})

	// Line charts are rendered in the browser from the Markdown table inside
	// <div class="line-chart"> (see src/js/LineChart.ts). So that readers do
	// not see the bare table until the script loads, put a static placeholder
	// of the chart before it - rendered for the mobile and the desktop content
	// width - and hide the table (it stays for screen readers).
	const lineChartPlaceholders = new Map();
	eleventyConfig.addTransform('lineChartPlaceholders', async function (content) {
		if (!this.page.outputPath || !this.page.outputPath.endsWith('.html') || !content.includes('<div class="line-chart">')) {
			return content;
		}

		const script = require('typescript').transpileModule(fs.readFileSync('./src/js/LineChart.ts', 'utf8'), {
			compilerOptions: { target: 'ES2019', module: 'ESNext' },
		}).outputText;
		let result = '';
		let lastIndex = 0;
		for (const match of content.matchAll(/<div class="line-chart">([\s\S]*?)<\/div>/g)) {
			const id = crypto.createHash('sha256').update(script).update('\0').update(match[0]).digest('hex');
			let placeholders = lineChartPlaceholders.get(id);
			if (placeholders === undefined) {
				const mobile = await renderLineChartPlaceholder(script, socialImageFontFace, match[0], 343);
				const desktop = await renderLineChartPlaceholder(script, socialImageFontFace, match[0], 688);
				placeholders = '<div class="sm:hidden" data-line-chart-placeholder aria-hidden="true">' + mobile + '</div>'
					+ '<div class="hidden sm:block" data-line-chart-placeholder aria-hidden="true">' + desktop + '</div>';
				lineChartPlaceholders.set(id, placeholders);
			}
			result += content.slice(lastIndex, match.index)
				+ '<div class="line-chart">' + placeholders + match[1].replace('<table>', '<table class="sr-only">') + '</div>';
			lastIndex = match.index + match[0].length;
		}

		return result + content.slice(lastIndex);
	});

	const { stdout: branchStdout } = await util.promisify(exec)('git rev-parse --abbrev-ref HEAD');
	const gitBranch = branchStdout.trim();
	eleventyConfig.addTransform("replaceBranch", function(content) {
		if (this.page.outputPath && (this.page.outputPath.endsWith(".html") || this.page.outputPath.endsWith(".xml"))) {
			return content.replaceAll("__BRANCH__", gitBranch);
		}
		return content;
	});

	eleventyConfig.on('eleventy.after', async () => {
		const matter = require('gray-matter');
		const errorsDir = __dirname + '/errors';
		const identifiers = JSON.parse(fs.readFileSync(__dirname + '/src/errorsIdentifiers.json', 'utf8'));
		const excluded = [];
		for (const identifier of Object.keys(identifiers)) {
			const docPath = errorsDir + '/' + identifier + '.md';
			if (!fs.existsSync(docPath)) continue;
			const file = matter(fs.readFileSync(docPath, 'utf8'));
			if (file.data.feasible === false || file.data.unlikely === true) {
				excluded.push(identifier);
			}
		}
		fs.writeFileSync(__dirname + '/tmp/excludedErrorIdentifiers.json', JSON.stringify(excluded));
	});

	return {
		dir: {
			input: "src",
			output: "tmp",
			layouts: "_layouts"
		}
	};
};
