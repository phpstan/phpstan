import {Compartment, Facet, RangeSetBuilder, StateEffect, StateEffectType, StateField, Text} from "@codemirror/state";
import {PHPStanError} from "../PHPStanError";
import {Decoration, DecorationSet, EditorView} from "@codemirror/view";

export const errorsFacet = Facet.define<PHPStanError[], PHPStanError[]>({
	combine: (values) => {
		return values.length > 0 ? values[0] : [];
	},
});

export const errorsCompartment = new Compartment();

export const updateErrorsEffect: StateEffectType<boolean> = StateEffect.define();

const buildErrorLines = (doc: Text, errors: PHPStanError[]) => {
	const errorLineDecoration = Decoration.line({class: 'bg-red-200/50 dark:bg-rose-400/50'});
	const tipLineDecoration = Decoration.line({class: 'bg-yellow-200/50 dark:bg-amber-600/50'});
	const builder = new RangeSetBuilder<Decoration>();
	const errorLines: number[] = [];
	const tipLines: number[] = [];
	for (const error of errors) {
		if (error.line < 1) {
			continue;
		}
		if (error.identifier && error.identifier.startsWith('phpstanPlayground.')) {
			tipLines.push(error.line - 1);
		} else {
			errorLines.push(error.line - 1);
		}
	}

	for (let i = 0; i < doc.lines; i++) {
		const line = doc.line(i + 1);
		if (errorLines.includes(line.number - 1)) {
			builder.add(line.from, line.from, errorLineDecoration);
		} else if (tipLines.includes(line.number - 1)) {
			builder.add(line.from, line.from, tipLineDecoration);
		}
	}

	return builder.finish();
};

export const lineErrors = StateField.define<DecorationSet>({
	create(state) {
		return buildErrorLines(state.doc, state.facet(errorsFacet));
	},
	update(lineErrors, transaction) {
		for (const effect of transaction.effects) {
			if (effect.is(updateErrorsEffect) && effect.value) {
				return buildErrorLines(transaction.state.doc, transaction.state.facet(errorsFacet));
			}
		}

		return lineErrors.map(transaction.changes);
	},
	provide(field) {
		return EditorView.decorations.from(field)
	},
});

