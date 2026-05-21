import {hoverTooltip} from '@codemirror/view';
import MarkdownIt from 'markdown-it';
import Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import {phpantomLsp, PHP_URI} from '../phpantom/lspClient';

// PHPantom returns LSP hover as Markdown (fenced ```php blocks for signatures,
// prose for docblock descriptions). Render it properly, with Prism syntax
// highlighting for the PHP code blocks (the prism-ghcolors theme is already
// imported globally in app.pcss, so tokens get coloured for free).
let md: MarkdownIt | null = null;
function markdown(): MarkdownIt {
	if (md === null) {
		md = new MarkdownIt({
			html: false,
			linkify: true,
			highlight: (str, lang) => {
				if (lang === 'php' && Prism.languages.php) {
					// Drop the redundant leading `<?php` PHPantom prepends to signatures.
					const code = str.replace(/^<\?php\n/, '');
					try {
						return Prism.highlight(code, Prism.languages.php, 'php');
					} catch {
						// fall through to default escaping
					}
				}
				return '';
			},
		});
	}
	return md;
}

interface LspHover {
	contents?: {value?: string} | string;
}

interface HoverParams {
	textDocument: {uri: string};
	position: {line: number; character: number};
}

// Live hover backed by PHPantom-in-wasm, over the shared LSP client. Separate
// from the PHPStan error hover (editor/hover.ts) — both tooltip sources coexist.
// We render hover ourselves (rather than lsp-client's hoverTooltips) to get
// Prism-highlighted code blocks.
export const phpantomHover = hoverTooltip(async (view, pos) => {
	const doc = view.state.doc;
	const line = doc.lineAt(pos);

	let hover: LspHover | null;
	try {
		hover = await phpantomLsp.request<HoverParams, LspHover | null>('textDocument/hover', {
			textDocument: {uri: PHP_URI},
			position: {line: line.number - 1, character: pos - line.from},
		});
	} catch {
		return null;
	}
	if (!hover) {
		return null;
	}

	const value = typeof hover.contents === 'string' ? hover.contents : hover.contents?.value;
	if (!value) {
		return null;
	}

	const html = markdown().render(value);

	return {
		pos,
		above: true,
		create() {
			const dom = document.createElement('div');
			dom.className = 'cm-phpantom-hover';
			dom.innerHTML = html;
			return {dom};
		},
	};
});
