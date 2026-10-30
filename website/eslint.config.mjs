import js from '@eslint/js';
import { defineConfig } from 'eslint/config';
import tseslint from 'typescript-eslint';

// tsc type-checks the value of every ko-* binding in TSX templates, but like
// data-* it accepts any attribute name with a dash, so a misspelled binding
// would only fail at runtime. This rule rejects ko-* attributes that the
// element's attribute type (KoBindings in src/js/jsx.d.ts) does not declare.
const knownBindings = {
	meta: {
		type: 'problem',
		messages: {
			unknown: 'Unknown binding "{{name}}": declare it in KoBindings or KoCustomBindings.',
		},
	},
	create(context) {
		const services = context.sourceCode.parserServices;
		const checker = services.program.getTypeChecker();

		return {
			JSXAttribute(node) {
				const name = node.name.name;
				if (typeof name !== 'string' || !name.startsWith('ko-')) {
					return;
				}

				const attributes = services.esTreeNodeToTSNodeMap.get(node.parent).attributes;
				const type = checker.getContextualType(attributes);
				if (type !== undefined && type.getProperty(name) === undefined) {
					context.report({node, messageId: 'unknown', data: {name}});
				}
			},
		};
	},
};

export default defineConfig(
	{
		files: ['src/js/**/*.{ts,tsx}'],
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
		plugins: {
			tko: {rules: {'known-bindings': knownBindings}},
		},
		rules: {
			'tko/known-bindings': 'error',
			'no-prototype-builtins': 'off',
			'@typescript-eslint/no-explicit-any': 'off',
			'@typescript-eslint/ban-ts-comment': [
				'error',
				{'ts-ignore': 'allow-with-description'},
			],
			// JSX is not supported by the core indent rule.
			'indent': ['error', 'tab', {'SwitchCase': 1, ignoredNodes: ['JSXElement *', 'JSXFragment *']}],
			'no-console': 'error',
		},
	},
);
