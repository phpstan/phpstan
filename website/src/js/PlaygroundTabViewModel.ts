import {PHPStanError} from './PHPStanError';
import {XRayData} from './XRayData';
import ko from '@tko/build.reference';

// The result of one analysis tab (a range of PHP versions).
export class PlaygroundTabViewModel {

	readonly errorsText: string;
	readonly errorLines: number[];
	readonly fixableCount: number;

	// AST X-Ray data for this tab's newest PHP version; null when the result
	// predates the feature (fetched on demand when the toggle is flipped).
	readonly xray: ko.Observable<XRayData | null>;

	// The code with all fixable errors fixed, in rounds like `phpstan analyse --fix`;
	// null when there is nothing to fix or the result predates the feature.
	constructor(readonly errors: PHPStanError[], readonly title: string, xray: XRayData | null = null, readonly fixedCode: string | null = null) {
		this.xray = ko.observable(xray);
		this.errorsText = `Found ${errors.length} ${errors.length === 1 ? 'error' : 'errors'}`;
		this.fixableCount = errors.filter((error) => error.fixable === true).length;
		this.errorLines = errors.filter((error) => error.line >= 1).map((error) => error.line - 1);
	}

	tabClass(active: boolean): string {
		const hasErrors = this.errors.length > 0;
		if (active) {
			return hasErrors ? 'bg-red-100 text-red-500 cursor-default border-gray-400' : 'bg-green-100 text-green-500 cursor-default border-gray-400';
		}

		return hasErrors ? 'bg-gray-100 text-red-400 hover:bg-white' : 'bg-gray-100 text-green-400 hover:bg-white';
	}

}
