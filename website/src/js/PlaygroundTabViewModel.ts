import {PHPStanError} from './PHPStanError';
import * as ko from 'knockout';

export class PlaygroundTabViewModel {

	errors: PHPStanError[];
	errorsText: string;
	errorLines: number[];
	tabClass: ko.PureComputed<string>;
	isActive: ko.Observable<boolean>;

	title: string;

	constructor(errors: PHPStanError[], title: string, active: boolean) {
		this.errors = errors;

		const errorsCount = this.errors.length;
		if (errorsCount === 1) {
			this.errorsText = 'Found ' + errorsCount.toString() + ' error';
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

		this.title = title;
		this.tabClass = ko.pureComputed((): string => {
			const isActive = this.isActive();
			if (isActive) {
				if (this.errors.length > 0) {
					return 'bg-red-100 text-red-500 cursor-default border-gray-400';
				}

				return 'bg-green-100 text-green-500 cursor-default border-gray-400';
			}

			if (this.errors.length > 0) {
				return 'bg-gray-100 text-red-400 hover:bg-white';
			}

			return 'bg-gray-100 text-green-400 hover:bg-white';
		});
		this.isActive = ko.observable(active);
	}

}
