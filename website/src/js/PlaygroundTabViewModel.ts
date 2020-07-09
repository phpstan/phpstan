import {PHPStanError} from './PHPStanError';

export class PlaygroundTabViewModel {

	errors: PHPStanError[];
	errorsText: string;
	errorLines: number[];

	title: string;

	constructor(errors: PHPStanError[]) {
		this.errors = errors;

		const errorsCount = this.errors.length;
		if (errorsCount === 1) {
			this.errorsText = 'Found ' + errorsCount.toString() + ' errors';
		} else {
			this.errorsText = 'Found ' + errorsCount.toString() + ' errors';
		}

		const lines = [];
		for (const error of errors) {
			const line = error.line;
			if (line < 1) {
				continue;
			}
			lines.push(line - 1);
		}

		this.errorLines = lines;

		this.title = 'PHP 7.4 (no errors)';
	}

}
