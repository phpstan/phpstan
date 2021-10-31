const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
	theme: {
		extend: {
			fontFamily: {
				sans: ['Inter var', ...defaultTheme.fontFamily.sans],
			},
		},
	},
	variants: {},
	plugins: [
		require('@tailwindcss/forms'),
		require('@tailwindcss/typography'),
		require('@tailwindcss/aspect-ratio'),
	],
	purge: {
		enabled: true,
		content: [
			'./tmp/*.html',
			'./tmp/user-guide/*.html',
			'./tmp/developing-extensions/*.html',
			'./tmp/blog.html',
			'./tmp/blog/*.html',
			'./tmp/js/PlaygroundTabViewModel.ts',
		],
		options: {
			whitelistPatterns: [/algolia/, /searchbox/, /littlefoot/],
		}
	},
}
