const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const pluginRss = require("@11ty/eleventy-plugin-rss");
const { DateTime } = require("luxon");
const readingTime = require('reading-time');
const mermaid = require("headless-mermaid");
const fs = require("fs");
const crypto = require("crypto");
const util = require("util");
const { exec } = require("child_process");
const captureWebsite = import("capture-website");
const { fixTypos } = require('typopo');
const anchor = require('markdown-it-anchor');
const nunjucks = require("nunjucks");

process.setMaxListeners(0);

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
		return readingTime(text).text;
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
		const svg = await mermaid.execute(content);
		const id = crypto.createHash('sha256').update(svg).digest('hex');
		const name = 'tmp/images/mermaid-' + id + '.svg';
		fs.writeFileSync(name, svg);

		return '<img class="mb-8" src="/images/mermaid-' + id + '.svg" />'
	});

	const nunjucksEnv = new nunjucks.Environment(new nunjucks.FileSystemLoader('.'));
	nunjucksEnv.addFilter('fixTypos', (text) => fixTypos(text, 'en-us'));
	eleventyConfig.addAsyncShortcode('socialImages', async function (title) {
		if (process.env.ELEVENTY_RUN_MODE === 'watch') {
			return '<meta name="twitter:image" content="/images/logo-big.png" />'
				+ "\n"
				+ '<meta property="og:image" content="/images/logo-big.png" />';
		}
		const content = nunjucksEnv.render('./src/_includes/social/socialImage.njk', {
			title: title,
			date: DateTime.fromJSDate(this.page.date, {zone: 'utc'}).toFormat('DDD'),
		});
		const capture = await captureWebsite;
		const image = await capture.default.buffer(content, {
			inputType: 'html',
			width: 800,
			height: 418,
			fullPage: true,
			launchOptions: {
				args: ['--no-sandbox'],
			},
		});
		const name = 'tmp/images/social-' + this.page.fileSlug + '.png';
		fs.writeFileSync(name, image);

		return '<meta name="twitter:image" content="/images/social-' + this.page.fileSlug + '.png" />'
			+ "\n"
			+ '<meta property="og:image" content="/images/social-' + this.page.fileSlug + '.png" />';
	})

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
