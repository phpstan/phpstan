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
					return 'bg-white text-red-600 border-gray-200 border-b-white cursor-default';
				}

				return 'bg-white text-green-600 border-gray-200 border-b-white cursor-default';
			}

			if (this.errors.length > 0) {
				return 'bg-gray-50 text-red-600 border-transparent hover:bg-gray-100';
			}

			return 'bg-gray-50 text-green-600 border-transparent hover:bg-gray-100';
		});
		this.isActive = ko.observable(active);
	}

}
