const { defineConfig } = require('eslint/config');
const js = require('@eslint/js');
const tseslint = require('typescript-eslint');

module.exports = defineConfig(
	js.configs.recommended,
	tseslint.configs.recommended,
	tseslint.configs.stylistic,
	{
		languageOptions: {
			parserOptions: {
				project: 'tsconfig.json',
				tsconfigRootDir: __dirname,
			},
		},
		rules: {
			"no-prototype-builtins": "off",
			"@typescript-eslint/no-explicit-any": "off",
			'@typescript-eslint/ban-ts-comment': [
				'error',
				{'ts-ignore': 'allow-with-description'},
			],
			"indent": ["error", "tab"],
			// console.error is how the handler logs to CloudWatch.
			"no-console": ["error", {"allow": ["error"]}]
		},
	},
);
