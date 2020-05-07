const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const pluginRss = require("@11ty/eleventy-plugin-rss");
const { DateTime } = require("luxon");
const readingTime = require('reading-time');

module.exports = function (eleventyConfig) {
	eleventyConfig.addPassthroughCopy('src/images');
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

	return {
		dir: {
			input: "src",
			output: "tmp",
			layouts: "_layouts"
		}
	};
};
