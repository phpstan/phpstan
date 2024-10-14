const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const pluginRss = require("@11ty/eleventy-plugin-rss");
const { DateTime } = require("luxon");
const readingTime = require('reading-time');
const mermaid = require("headless-mermaid");
const fs = require("fs");
const crypto = require("crypto");
const { EleventyRenderPlugin } = require("@11ty/eleventy");
const captureWebsite = import("capture-website");
const { fixTypos } = require('typopo');

process.setMaxListeners(0);

module.exports = function (eleventyConfig) {
	eleventyConfig.addPassthroughCopy('src/images');
	eleventyConfig.addPassthroughCopy('src/images-emails');
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
	markdownLib.use(require('markdown-it-anchor'), {
		level: 2,
		permalink: true,
		permalinkSymbol: '#',
		permalinkClass: 'header-anchor ml-1 text-gray-300 hover:text-black',
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

	const inspect = require("util").inspect;
	eleventyConfig.addFilter("debug", (content) => `<pre>${inspect(content)}</pre>`);

	eleventyConfig.addPairedShortcode('mermaid', async (content) => {
		const svg = await mermaid.execute(content);
		const id = crypto.createHash('sha256').update(svg).digest('hex');
		const name = 'tmp/images/mermaid-' + id + '.svg';
		fs.writeFileSync(name, svg);

		return '<img class="mb-8" src="/' + name + '" />'
	});

	eleventyConfig.addAsyncShortcode('socialImages', async function (title) {
		if (process.env.ELEVENTY_RUN_MODE === 'watch') {
			return '<meta name="twitter:image" content="/tmp/images/logo-big.png" />'
				+ "\n"
				+ '<meta property="og:image" content="/tmp/images/logo-big.png" />';
		}
		const content = await eleventyConfig.nunjucksAsyncShortcodes.renderFile('./src/_includes/social/socialImage.njk', {
			title: title,
			date: DateTime.fromJSDate(this.page.date, {zone: 'utc'}).toFormat('DDD'),
		}, 'njk');
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

		return '<meta name="twitter:image" content="/' + name + '" />'
			+ "\n"
			+ '<meta property="og:image" content="/' + name + '" />';
	})

	return {
		dir: {
			input: "src",
			output: "tmp",
			layouts: "_layouts"
		}
	};
};
