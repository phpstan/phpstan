const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
	theme: {
		extend: {
			fontFamily: {
				sans: ['Inter Variable', ...defaultTheme.fontFamily.sans],
			},
		},
	},
	content: [
		'./tmp/*.html',
		'./tmp/user-guide/*.html',
		'./tmp/developing-extensions/*.html',
		'./tmp/blog.html',
		'./tmp/blog/*.html',
		'./tmp/js/**/*.ts',
	],
	darkMode: 'selector'
}
