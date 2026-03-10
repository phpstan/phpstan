import {Facet, StateEffect, StateField, EditorState, Extension} from '@codemirror/state';
import {invertedEffects} from '@codemirror/commands';

export const setUrlId = StateEffect.define<string | null>();

const initialUrlIdFacet = Facet.define<string | null, string | null>({
	combine: values => values.length ? values[0] : null,
});

export const urlIdField = StateField.define<string | null>({
	create: (state) => state.facet(initialUrlIdFacet),
	update(value, tr) {
		for (const effect of tr.effects) {
			if (effect.is(setUrlId)) return effect.value;
		}
		return value;
	},
	toJSON(value) { return value; },
	fromJSON(json) { return json; },
});

// Record inverse effects so undo/redo restores the previous URL id
const urlIdInverter = invertedEffects.of((tr) => {
	const effects: StateEffect<string | null>[] = [];
	for (const effect of tr.effects) {
		if (effect.is(setUrlId)) {
			effects.push(setUrlId.of(tr.startState.field(urlIdField)));
		}
	}
	return effects;
});

// Auto-clear URL id on any doc change (first edit only, since field is null after)
const clearUrlIdOnEdit = EditorState.transactionExtender.of((tr) => {
	if (tr.docChanged && !tr.effects.some(e => e.is(setUrlId))
		&& tr.startState.field(urlIdField) !== null) {
		return {effects: [setUrlId.of(null)]};
	}
	return null;
});

// Bundle all extensions; optionally include initial URL id
export function urlIdExtensions(initialUrlId: string | null): Extension[] {
	const exts: Extension[] = [urlIdField, urlIdInverter, clearUrlIdOnEdit];
	if (initialUrlId !== null) {
		exts.push(initialUrlIdFacet.of(initialUrlId));
	}
	return exts;
}
