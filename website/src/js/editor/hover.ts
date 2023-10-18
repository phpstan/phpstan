import {hoverTooltip} from "@codemirror/view";
import {PHPStanError} from "../PHPStanError";
import {errorsFacet} from "./errors";

export const hover = hoverTooltip((view, pos, side) => {
	const currentErrors: PHPStanError[] = view.state.facet(errorsFacet);
	const line = view.state.doc.lineAt(pos);
	const lineErrors: PHPStanError[] = [];
	for (const error of currentErrors) {
		if (error.line === line.number) {
			lineErrors.push(error);
		}
	}

	if (lineErrors.length === 0) {
		return null;
	}

	let from = line.from;
	while (from < line.to && /\s/.test(line.text[from - line.from])) {
		from++
	}

	if (from === line.to) {
		from = line.from
	}

	return {
		pos: from,
		end: line.to,
		above: true,
		create() {
			const dom = document.createElement('div')
			let style = 'bg-yellow-100 text-black';
			for (const error of lineErrors) {
				const errorDom = document.createElement('div');
				errorDom.className = '';
				errorDom.textContent = error.message;
				dom.appendChild(errorDom);

				if (!error.identifier || !error.identifier.startsWith('phpstanPlayground.')) {
					style = 'bg-red-800 text-white';
				}
			}

			dom.className = 'rounded-lg px-3 py-1 border-0 text-xs flex flex-col gap-2 ' + style;

			const outerDom = document.createElement('div');
			outerDom.className = 'pb-1';
			outerDom.appendChild(dom);

			return {
				dom: outerDom,
			}
		}
	}
}, {
	hoverTime: 5,
});
