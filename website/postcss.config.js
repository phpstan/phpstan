const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');

const IS_DEVELOPMENT = process.env.NODE_ENV === 'development';

const plugins = [];
plugins.push(tailwindcss)
plugins.push(tailwindcss(__dirname + '/tailwind.config.js'))
plugins.push(autoprefixer)

if (!IS_DEVELOPMENT) {
	const purgecss = require('@fullhuman/postcss-purgecss');

	plugins.push(
		purgecss({
			content: [__dirname + '/*.html'],
			defaultExtractor: content => content.match(/[\w-/.:]+(?<!:)/g) || [],
			whitelistPatterns: [/^hljs/],
		})
	);
}

module.exports = { plugins }
