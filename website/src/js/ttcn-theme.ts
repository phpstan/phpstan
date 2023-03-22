import {HighlightStyle, syntaxHighlighting} from '@codemirror/language'
import {tags as t} from '@lezer/highlight'

// helpful sources:
// https://github.com/lezer-parser/php/blob/96115e48b82f1ac6f0e849ff032645b812bd064b/src/highlight.js
// https://github.com/lezer-parser/php/blob/main/src/php.grammar

export const ttcnHighlightStyle = HighlightStyle.define([
	{tag: t.quote,
		color: '#090'},
	{tag: t.deleted,
		color: '#d44'},
	{tag: t.inserted,
		color: '#292'},
	{tag: t.strong,
		fontWeight: 'bold'},
	{tag: t.emphasis,
		fontStyle: 'italic'},
	{tag: t.link,
		color: '#00c',
		textDecoration: 'underline'},
	{tag: t.strikethrough,
		textDecoration: 'line-through'},
	{tag: t.heading,
		fontWeight: 'bold',
		color: '#00f'},

	{tag: t.atom,
		color: '#219'},
	{tag: t.attributeName,
		color: '#00c'},
	{tag: t.comment,
		color: '#333'},
	{tag: [t.definition(t.variableName), t.className, t.name],
		color: '#00f'},
	{tag: t.invalid,
		color: '#f00'},
	{tag: t.keyword,
		fontWeight: 'bold'},
	{tag: t.meta,
		color: '#555'},
	{tag: t.string,
		color: '#060'},
	{tag: [t.special(t.string), t.escape, t.character],
		color: '#f50'},
	{tag: t.tagName,
		color: '#170'},
	{tag: [t.variableName, t.propertyName, t.special(t.propertyName)],
		color: '#05a'},
	{tag: [t.function(t.variableName), t.special(t.variableName), t.local(t.variableName), t.typeName],
		color: '#8b2252'},
	{tag: [t.self, t.labelName],
		color: '#085'}
])

export const ttcn = syntaxHighlighting(ttcnHighlightStyle);
