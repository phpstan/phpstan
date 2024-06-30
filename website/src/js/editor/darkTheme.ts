// from https://github.com/dennis84/codemirror-themes/tree/main/theme
// Copyright (C) 2018-2021 by Dennis Dietrich <ddietr@protonmail.com> and others

import {EditorView} from '@codemirror/view'
import {Extension} from '@codemirror/state'
import {HighlightStyle, syntaxHighlighting} from '@codemirror/language'
import {tags as t} from '@lezer/highlight'

export const config = {
	name: 'materialDark',
	dark: true,
	background: '#263238',
	foreground: '#EEFFFF',
	selection: '#80CBC420',
	cursor: '#FFCC00',
	dropdownBackground: '#263238',
	dropdownBorder: '#FFFFFF10',
	activeLine: '#4c616c22',
	matchingBracket: '#263238',
	keyword: '#89DDFF',
	storage: '#89DDFF',
	variable: '#EEFFFF',
	parameter: '#EEFFFF',
	function: '#82AAFF',
	string: '#C3E88D',
	constant: '#89DDFF',
	type: '#FFCB6B',
	class: '#FFCB6B',
	number: '#F78C6C',
	comment: '#546E7A',
	heading: '#89DDFF',
	invalid: '#f0717870',
	regexp: '#C3E88D',
}

export const materialDarkTheme = EditorView.theme({
	'&': {
		color: config.foreground,
		backgroundColor: config.background,
	},

	'.cm-content': {caretColor: config.cursor},

	'.cm-cursor, .cm-dropCursor': {borderLeftColor: config.cursor},
	'&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground, .cm-selectionBackground, .cm-content ::selection': {backgroundColor: config.selection},

	'.cm-panels': {backgroundColor: config.dropdownBackground, color: config.foreground},
	'.cm-panels.cm-panels-top': {borderBottom: '2px solid black'},
	'.cm-panels.cm-panels-bottom': {borderTop: '2px solid black'},

	'.cm-searchMatch': {
		backgroundColor: config.dropdownBackground,
		outline: `1px solid ${config.dropdownBorder}`
	},
	'.cm-searchMatch.cm-searchMatch-selected': {
		backgroundColor: config.selection
	},

	'.cm-activeLine': {backgroundColor: config.activeLine},
	'.cm-selectionMatch': {backgroundColor: config.selection},

	'&.cm-focused .cm-matchingBracket, &.cm-focused .cm-nonmatchingBracket': {
		backgroundColor: config.matchingBracket,
		outline: 'none'
	},

	'.cm-gutters': {
		backgroundColor: config.background,
		color: config.foreground,
		border: 'none'
	},
	'.cm-activeLineGutter': {backgroundColor: config.background},

	'.cm-foldPlaceholder': {
		backgroundColor: 'transparent',
		border: 'none',
		color: config.foreground
	},
	'.cm-tooltip': {
		border: `1px solid ${config.dropdownBorder}`,
		backgroundColor: config.dropdownBackground,
		color: config.foreground,
	},
	'.cm-tooltip .cm-tooltip-arrow:before': {
		borderTopColor: 'transparent',
		borderBottomColor: 'transparent'
	},
	'.cm-tooltip .cm-tooltip-arrow:after': {
		borderTopColor: config.foreground,
		borderBottomColor: config.foreground,
	},
	'.cm-tooltip-autocomplete': {
		'& > ul > li[aria-selected]': {
			background: config.selection,
			color: config.foreground,
		}
	}
}, {dark: config.dark})

export const materialDarkHighlightStyle = HighlightStyle.define([
	{tag: t.keyword, color: config.keyword},
	{tag: [t.name, t.deleted, t.character, t.macroName], color: config.variable},
	{tag: [t.propertyName], color: config.function},
	{tag: [t.processingInstruction, t.string, t.inserted, t.special(t.string)], color: config.string},
	{tag: [t.function(t.variableName), t.labelName], color: config.function},
	{tag: [t.color, t.constant(t.name), t.standard(t.name)], color: config.constant},
	{tag: [t.definition(t.name), t.separator], color: config.variable},
	{tag: [t.className], color: config.class},
	{tag: [t.number, t.changed, t.annotation, t.modifier, t.self, t.namespace], color: config.number},
	{tag: [t.typeName], color: config.type, fontStyle: config.type},
	{tag: [t.operator, t.operatorKeyword], color: config.keyword},
	{tag: [t.url, t.escape, t.regexp, t.link], color: config.regexp},
	{tag: [t.meta, t.comment], color: config.comment},
	{tag: t.strong, fontWeight: 'bold'},
	{tag: t.emphasis, fontStyle: 'italic'},
	{tag: t.link, textDecoration: 'underline'},
	{tag: t.heading, fontWeight: 'bold', color: config.heading},
	{tag: [t.atom, t.bool, t.special(t.variableName)], color: config.variable},
	{tag: t.invalid, color: config.invalid},
	{tag: t.strikethrough, textDecoration: 'line-through'},
])

export const materialDark: Extension = [
	materialDarkTheme,
	syntaxHighlighting(materialDarkHighlightStyle),
]
