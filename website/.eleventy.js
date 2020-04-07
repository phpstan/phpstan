const syntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");

module.exports = function (eleventyConfig) {
	eleventyConfig.addPassthroughCopy('src/images');
	eleventyConfig.addPassthroughCopy('src/js');
	eleventyConfig.addPassthroughCopy('src/app.pcss');
	eleventyConfig.addPassthroughCopy('src/robots.txt');
	eleventyConfig.addPlugin(syntaxHighlight);

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
	});

	eleventyConfig.setLibrary("md", markdownLib);

	eleventyConfig.addFilter("trimInputPath", function(value) {
		if (value.startsWith('./')) {
			return value.substring(2);
		}

		return value;
	});

	return {
		dir: {
			input: "src",
			output: "tmp",
			layouts: "_layouts"
		}
	};
};
