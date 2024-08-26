import {captureException, init as SentryInit} from '@sentry/node';
import {AWSError, Lambda, S3} from 'aws-sdk';
import {PromiseResult} from 'aws-sdk/lib/request';
import middy from 'middy';
import { cors } from 'middy/middlewares';
import { v4 as uuid } from 'uuid';

SentryInit({
	dsn: 'https://f56a0e1f5022472982e901e7a5d08514@sentry.io/1319481',
});

interface HttpRequest {
	body: string;
	queryStringParameters: any;
}

interface HttpResponse {
	statusCode: number;
	body?: string;
}

interface PHPStanError {
	message: string,
	line: number,
	tip?: string,
	identifier?: string,
	ignorable?: boolean,
}

const lambda = new Lambda();
const s3 = new S3();

async function analyseResultInternal(
	code: string,
	level: string,
	runStrictRules: boolean,
	runBleedingEdge: boolean,
	treatPhpDocTypesAsCertain: boolean,
	phpVersions: number[],
): Promise<any[]> {
	const lambdaPromises: [Promise<PromiseResult<Lambda.InvocationResponse, AWSError>>, number][] = [];
	for (const phpVersion of phpVersions) {
		lambdaPromises.push([lambda.invoke({
			FunctionName: 'phpstan-runner-prod-main',
			Payload: JSON.stringify({
				code: code,
				level: level,
				strictRules: runStrictRules,
				bleedingEdge: runBleedingEdge,
				treatPhpDocTypesAsCertain: treatPhpDocTypesAsCertain,
				phpVersion: phpVersion,
			}),
		}).promise(), phpVersion]);
	}

	const versionedErrors: any[] = [];
	for (const tuple of lambdaPromises) {
		const promise = tuple[0];
		const phpVersion = tuple[1];
		const lambdaResult = await promise;

		const jsonResponse = JSON.parse(lambdaResult.Payload as string);
		versionedErrors.push({
			phpVersion: phpVersion,
			errors: jsonResponse.result.map((error: any): PHPStanError => {
				const obj: PHPStanError = {
					line: error.line,
					message: error.message,
					ignorable: error.ignorable,
				};
				if (error.tip) {
					obj.tip = error.tip;
				}

				if (error.identifier) {
					obj.identifier = error.identifier;
				}

				return obj;
			}),
		});
	}

	return versionedErrors;
}

function createTabs(versionedErrors: {phpVersion: number, errors: PHPStanError[]}[]): any[] {
	const versions: {versions: number[], errors: PHPStanError[]}[] = [];
	let last: {versions: number[], errors: PHPStanError[]} | null = null;
	for (const version of versionedErrors) {
		const phpVersion = version.phpVersion;
		const errors = version.errors;
		const current = {
			versions: [phpVersion],
			errors,
		};
		if (last === null) {
			last = current;
			continue;
		}

		if (errors.length !== last.errors.length) {
			versions.push(last);
			last = current;
			continue;
		}

		let merge = true;
		for (const i in errors) {
			if (!errors.hasOwnProperty(i)) {
				continue;
			}
			const error = errors[i];
			const lastError = last.errors[i];
			if (error.line !== lastError.line) {
				versions.push(last);
				last = current;
				merge = false;
				break;
			}
			if (error.message !== lastError.message) {
				versions.push(last);
				last = current;
				merge = false;
				break;
			}
			if (error.tip !== lastError.tip) {
				versions.push(last);
				last = current;
				merge = false;
				break;
			}
			if (error.identifier !== lastError.identifier) {
				versions.push(last);
				last = current;
				merge = false;
				break;
			}
			if (error.ignorable !== lastError.ignorable) {
				versions.push(last);
				last = current;
				merge = false;
				break;
			}
		}

		if (!merge) {
			continue;
		}

		last.versions.push(phpVersion);
	}

	if (last !== null) {
		versions.push(last);
	}

	versions.sort((a: {versions: number[], errors: PHPStanError[]}, b: {versions: number[], errors: PHPStanError[]}) => {
		const aVersion = a.versions[a.versions.length - 1];
		const bVersion = b.versions[b.versions.length - 1];

		return bVersion - aVersion;
	});

	const tabs: any[] = [];
	const versionNumberToString = (version: number): string => {
		const first = Math.floor(version / 10000);
		const second = Math.floor((version % 10000) / 100);
		const third = Math.floor(version % 100);

		return first + '.' + second + (third !== 0 ? '.' + third : '');
	}
	for (const version of versions) {
		let title = 'PHP ';
		if (version.versions.length > 1) {
			title += versionNumberToString(version.versions[0]);
			title += ' – ';
			title += versionNumberToString(version.versions[version.versions.length - 1]);
		} else {
			title += versionNumberToString(version.versions[0]);
		}

		if (version.errors.length === 1) {
			title += ' (1 error)';
		} else if (version.errors.length > 0) {
			title += ' (' + version.errors.length + ' errors)';
		}
		tabs.push({
			errors: version.errors,
			title: title,
		});
	}

	return tabs;
}

async function analyseResult(request: HttpRequest): Promise<HttpResponse> {
	try {
		const json = JSON.parse(request.body);
		const runStrictRules = typeof json.strictRules !== 'undefined' ? json.strictRules : false;
		const runBleedingEdge = typeof json.bleedingEdge !== 'undefined' ? json.bleedingEdge : false;
		const treatPhpDocTypesAsCertain = typeof json.treatPhpDocTypesAsCertain !== 'undefined' ? json.treatPhpDocTypesAsCertain : true;
		const saveResult: boolean = typeof json.saveResult !== 'undefined' ? json.saveResult : true;

		const versionedErrors = await analyseResultInternal(
			json.code,
			json.level,
			runStrictRules,
			runBleedingEdge,
			treatPhpDocTypesAsCertain,
			[70200, 70300, 70400, 80000, 80100, 80200, 80300, 80400],
		);
		const response: any = {
			tabs: createTabs(versionedErrors),
			versionedErrors,
		};

		if (saveResult) {
			const id: string = uuid() as string;
			await s3.putObject({
				Bucket: 'phpstan-playground',
				Key: 'api/results/' + id + '.json',
				ContentType: 'application/json',
				Body: JSON.stringify({
					code: json.code,
					versionedErrors: versionedErrors,
					version: 'N/A',
					level: json.level,
					config: {
						strictRules: runStrictRules,
						bleedingEdge: runBleedingEdge,
						treatPhpDocTypesAsCertain: treatPhpDocTypesAsCertain,
					},
				}),
			}).promise();

			response.id = id;
		}

		return Promise.resolve({
			statusCode: 200,
			body: JSON.stringify(response),
		});
	} catch (e) {
		console.error(e);
		captureException(e);
		return Promise.resolve({statusCode: 500});
	}
}

async function retrieveResult(request: HttpRequest): Promise<HttpResponse> {
	try {
		const id = request.queryStringParameters.id;
		const object = await s3.getObject({
			Bucket: 'phpstan-playground',
			Key: 'api/results/' + id + '.json',
		}).promise();
		const json = JSON.parse(object.Body as string);
		const strictRules = typeof json.config.strictRules !== 'undefined' ? json.config.strictRules : false;
		const bleedingEdge = typeof json.config.bleedingEdge !== 'undefined' ? json.config.bleedingEdge : false;
		const treatPhpDocTypesAsCertain = typeof json.config.treatPhpDocTypesAsCertain !== 'undefined' ? json.config.treatPhpDocTypesAsCertain : true;

		let phpVersionsToAnalyse: number[] = [70200, 70300, 70400, 80000];
		if (typeof json.versionedErrors !== 'undefined') {
			phpVersionsToAnalyse = json.versionedErrors.map((errors: {phpVersion: number, errors: PHPStanError[]}) => {
				return errors.phpVersion;
			});
		}

		if (!phpVersionsToAnalyse.includes(80100)) {
			phpVersionsToAnalyse.push(80100);
		}
		if (!phpVersionsToAnalyse.includes(80200)) {
			phpVersionsToAnalyse.push(80200);
		}
		if (!phpVersionsToAnalyse.includes(80300)) {
			phpVersionsToAnalyse.push(80300);
		}
		if (!phpVersionsToAnalyse.includes(80400)) {
			phpVersionsToAnalyse.push(80400);
		}

		const newResult = await analyseResultInternal(
			json.code,
			json.level,
			strictRules,
			bleedingEdge,
			treatPhpDocTypesAsCertain,
			phpVersionsToAnalyse,
		);
		const newTabs = createTabs(newResult);

		const bodyJson: any = {
			code: json.code,
			errors: json.errors,
			version: json.version,
			level: json.level,
			config: {
				strictRules,
				bleedingEdge,
				treatPhpDocTypesAsCertain,
			},
			upToDateTabs: newTabs,
			upToDateVersionedErrors: newResult,
		};

		if (typeof json.versionedErrors !== 'undefined') {
			bodyJson.versionedErrors = json.versionedErrors;
		} else {
			bodyJson.versionedErrors = [{phpVersion: 70400, errors: json.errors}];
		}
		if (typeof json.versionedErrors !== 'undefined') {
			bodyJson.tabs = createTabs(json.versionedErrors);

			const originalPhpVersions: number[] = json.versionedErrors.map((errors: {phpVersion: number, errors: PHPStanError[]}) => {
				return errors.phpVersion;
			});
			const filteredNewResult = newResult.filter((errors) => {
				return originalPhpVersions.indexOf(errors.phpVersion) !== -1;
			});
			const filteredNewTabs = createTabs(filteredNewResult);
			if (filteredNewTabs.length === newTabs.length) {
				const firstFilteredNewTab = filteredNewTabs[0];
				const firstNewTab = newTabs[0];
				if (firstFilteredNewTab.errors.length === firstNewTab.errors.length) {
					let isSame = true;
					for (let i = 0; i < firstFilteredNewTab.errors.length; i++) {
						const error = firstFilteredNewTab.errors[i];
						const otherError = firstNewTab.errors[i];

						if (error.line !== otherError.line) {
							isSame = false;
							break;
						}

						if (error.message !== otherError.message) {
							isSame = false;
							break;
						}

						if (error.tip !== otherError.tip) {
							isSame = false;
							break;
						}

						if (error.identifier !== otherError.identifier) {
							isSame = false;
							break;
						}

						if (error.ignorable !== otherError.ignorable) {
							isSame = false;
							break;
						}
					}

					if (isSame) {
						bodyJson.upToDateTabs = filteredNewTabs;
					}
				}
			}
		}
		return Promise.resolve({
			statusCode: 200,
			body: JSON.stringify(bodyJson),
		});
	} catch (e) {
		console.error(e);
		captureException(e);
		return Promise.resolve({statusCode: 500});
	}
}

async function retrieveSample(request: HttpRequest): Promise<HttpResponse> {
	try {
		const id = request.queryStringParameters.id;
		const object = await s3.getObject({
			Bucket: 'phpstan-playground',
			Key: 'api/results/' + id + '.json',
		}).promise();
		const json = JSON.parse(object.Body as string);
		const strictRules = typeof json.config.strictRules !== 'undefined' ? json.config.strictRules : false;
		const bleedingEdge = typeof json.config.bleedingEdge !== 'undefined' ? json.config.bleedingEdge : false;
		const treatPhpDocTypesAsCertain = typeof json.config.treatPhpDocTypesAsCertain !== 'undefined' ? json.config.treatPhpDocTypesAsCertain : true;

		const bodyJson: any = {
			code: json.code,
			errors: json.errors,
			version: json.version,
			level: json.level,
			config: {
				strictRules,
				bleedingEdge,
				treatPhpDocTypesAsCertain,
			},
		};
		if (typeof json.versionedErrors !== 'undefined') {
			bodyJson.versionedErrors = json.versionedErrors;
		} else {
			bodyJson.versionedErrors = [{phpVersion: 70400, errors: json.errors}];
		}
		return Promise.resolve({
			statusCode: 200,
			body: JSON.stringify(bodyJson),
		});
	} catch (e) {
		console.error(e);
		captureException(e);
		return Promise.resolve({statusCode: 500});
	}
}

async function retrieveLegacyResult(request: HttpRequest): Promise<HttpResponse> {
	try {
		const id = request.queryStringParameters.id;
		const firstTwoChars = id.substr(0, 2);
		const path = 'data/results/' + firstTwoChars + '/' + id;
		const inputObject = await s3.getObject({
			Bucket: 'phpstan-playground',
			Key: path + '/input.json',
		}).promise();
		const outputObject = await s3.getObject({
			Bucket: 'phpstan-playground',
			Key: path + '/output.json',
		}).promise();
		const inputJson = JSON.parse(inputObject.Body as string);
		const AnsiToHtml = require('ansi-to-html');
		const convert = new AnsiToHtml();
		const result = await analyseResultInternal(
			inputJson.phpCode,
			inputJson.level.toString(),
			false,
			false,
			true,
			[70200, 70300, 70400, 80000, 80100, 80200, 80300, 80400],
		);

		return Promise.resolve({
			statusCode: 200,
			body: JSON.stringify({
				code: inputJson.phpCode,
				htmlErrors: convert.toHtml(JSON.parse(outputObject.Body as string).output),
				upToDateTabs: createTabs(result),
				upToDateVersionedErrors: result,
				version: inputJson.phpStanVersion,
				level: inputJson.level.toString(),
				config: {
					strictRules: false,
					bleedingEdge: false,
					treatPhpDocTypesAsCertain: true,
				},
			}),
		});
	} catch (e) {
		console.error(e);
		captureException(e);
		return Promise.resolve({statusCode: 500});
	}
}

const corsMiddleware = cors();

module.exports = {
	analyseResult: middy(analyseResult).use(corsMiddleware),
	retrieveResult: middy(retrieveResult).use(corsMiddleware),
	retrieveSample: middy(retrieveSample).use(corsMiddleware),
	retrieveLegacyResult: middy(retrieveLegacyResult).use(corsMiddleware),
};
