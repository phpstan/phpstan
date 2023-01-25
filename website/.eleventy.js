const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const pluginRss = require("@11ty/eleventy-plugin-rss");
const { DateTime } = require("luxon");
const readingTime = require('reading-time');
const mermaid = require("headless-mermaid");
const fs = require("fs");
const crypto = require("crypto");

module.exports = function (eleventyConfig) {
	eleventyConfig.addPassthroughCopy('src/images');
	eleventyConfig.addPassthroughCopy('src/images-emails');
	eleventyConfig.addPassthroughCopy('src/js');
	eleventyConfig.addPassthroughCopy('src/app.pcss');
	eleventyConfig.addPassthroughCopy('src/robots.txt');
	eleventyConfig.addPlugin(syntaxHighlight);
	eleventyConfig.addPlugin(pluginRss);
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

	eleventyConfig.addShortcode("year", () => {
		return new Date().getFullYear().toString();
	});

	eleventyConfig.addPairedNunjucksShortcode("markdown", (content) => {
		return markdownLib.render(content);
	});

	eleventyConfig.addPairedShortcode('mermaid', async (content) => {
		const svg = await mermaid.execute(content);
		const id = crypto.createHash('sha256').update(svg).digest('hex');
		const name = 'tmp/images/mermaid-' + id + '.svg';
		fs.writeFileSync(name, svg);

		return '<img class="mb-8" src="/' + name + '" />'
	});

	return {
		dir: {
			input: "src",
			output: "tmp",
			layouts: "_layouts"
		}
	};
};
