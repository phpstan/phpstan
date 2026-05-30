import js from '@eslint/js';
import tseslint from 'typescript-eslint';

export default tseslint.config(
	{
		ignores: ['dist/', 'tmp/', 'node_modules/'],
	},
	{
		files: ['**/*.ts'],
		extends: [
			js.configs.recommended,
			...tseslint.configs.recommended,
			...tseslint.configs.stylistic,
		],
		languageOptions: {
			parserOptions: {
				project: 'tsconfig.json',
				tsconfigRootDir: import.meta.dirname,
			},
		},
		rules: {
			'no-prototype-builtins': 'off',
			'@typescript-eslint/no-explicit-any': 'off',
			'@typescript-eslint/ban-ts-comment': [
				'error',
				{ 'ts-ignore': 'allow-with-description' },
			],
			'indent': ['error', 'tab', { SwitchCase: 1 }],
			'no-console': 'error',
		},
	},
);
