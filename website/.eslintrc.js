module.exports = {
	"extends": [
		'eslint:recommended',
		'plugin:@typescript-eslint/recommended',
		'plugin:@typescript-eslint/stylistic',
	],
	"parser": "@typescript-eslint/parser",
	"parserOptions": {
		"project": "tsconfig.json",
		"sourceType": "module"
	},
	"plugins": [
		"@typescript-eslint"
	],
	"root": true,
	"rules": {
		"no-prototype-builtins": "off",
		"@typescript-eslint/no-explicit-any": "off",
		'@typescript-eslint/ban-ts-comment': [
			'error',
			{'ts-ignore': 'allow-with-description'},
		],
		"indent": ["error", "tab"],
		"no-console": "error"
	}
};
