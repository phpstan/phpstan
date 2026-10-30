import {PHPStanError} from './PHPStanError';
import {XRayData} from './XRayData';
import ko from '@tko/build.reference';

// The result of one analysis tab (a range of PHP versions).
export class PlaygroundTabViewModel {

	readonly errorsText: string;
	readonly errorLines: number[];
	readonly isActive: ko.Observable<boolean>;

	// AST X-Ray data for this tab's newest PHP version; null when the result
	// predates the feature (fetched on demand when the toggle is flipped).
	readonly xray: ko.Observable<XRayData | null>;

	readonly tabClass = ko.pureComputed((): string => {
		const hasErrors = this.errors.length > 0;
		if (this.isActive()) {
			return hasErrors ? 'bg-red-100 text-red-500 cursor-default border-gray-400' : 'bg-green-100 text-green-500 cursor-default border-gray-400';
		}

		return hasErrors ? 'bg-gray-100 text-red-400 hover:bg-white' : 'bg-gray-100 text-green-400 hover:bg-white';
	});

	constructor(readonly errors: PHPStanError[], readonly title: string, active: boolean, xray: XRayData | null = null) {
		this.xray = ko.observable(xray);
		this.isActive = ko.observable(active);
		this.errorsText = `Found ${errors.length} ${errors.length === 1 ? 'error' : 'errors'}`;
		this.errorLines = errors.filter((error) => error.line >= 1).map((error) => error.line - 1);
	}

}
