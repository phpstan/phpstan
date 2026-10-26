import js from '@eslint/js';
import { defineConfig } from 'eslint/config';
import tseslint from 'typescript-eslint';

export default defineConfig(
	{
		files: ['src/js/**/*.ts'],
		extends: [
			js.configs.recommended,
			tseslint.configs.recommended,
			tseslint.configs.stylistic,
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
				{'ts-ignore': 'allow-with-description'},
			],
			'indent': ['error', 'tab', {'SwitchCase': 1}],
			'no-console': 'error',
		},
	},
);
