import {captureException, init as SentryInit} from '@sentry/node';
import {AWSError, Lambda, S3} from 'aws-sdk';
import {PromiseResult} from 'aws-sdk/lib/request';
import { v4 as uuid } from 'uuid';
import { z } from 'zod';

SentryInit({
	dsn: 'https://f56a0e1f5022472982e901e7a5d08514@sentry.io/1319481',
});

const optionsSchema = z.object({
	inferPrivatePropertyTypeFromConstructor: z.boolean().optional(),
	rememberPossiblyImpureFunctionValues: z.boolean().optional(),
	checkBenevolentUnionTypes: z.boolean().optional(),
	checkTooWideTypesInProtectedAndPublicMethods: z.boolean().optional(),
	implicitThrows: z.boolean().optional(),
	reportUncheckedExceptionDeadCatch: z.boolean().optional(),
	uncheckedExceptionClasses: z.array(z.string()).optional(),
	checkedExceptionClasses: z.array(z.string()).optional(),
	missingCheckedExceptionInThrows: z.boolean().optional(),
	tooWideImplicitThrowType: z.boolean().optional(),
	reportUnsafeArrayStringKeyCasting: z.enum(['detect', 'prevent']).nullable().optional(),
}).passthrough();

type PlaygroundOptions = z.infer<typeof optionsSchema>;

function validateOptions(input: unknown): PlaygroundOptions | undefined {
	if (input === undefined || input === null) {
		return undefined;
	}
	if (typeof input !== 'object') {
		return undefined;
	}
	return optionsSchema.parse(input);
}

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

const ALL_PHP_VERSIONS: number[] = [70200, 70300, 70400, 80000, 80100, 80200, 80300, 80400, 80500];

function errorsEqual(a: PHPStanError, b: PHPStanError): boolean {
	return a.line === b.line
		&& a.message === b.message
		&& a.tip === b.tip
		&& a.identifier === b.identifier
		&& a.ignorable === b.ignorable;
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
	options?: PlaygroundOptions,
): Promise<any[]> {
	const lambdaPromises: [Promise<PromiseResult<Lambda.InvocationResponse, AWSError>>, number][] = [];
	for (const phpVersion of phpVersions) {
		const payload: any = {
			code: code,
			level: level,
			strictRules: runStrictRules,
			bleedingEdge: runBleedingEdge,
			treatPhpDocTypesAsCertain: treatPhpDocTypesAsCertain,
			phpVersion: phpVersion,
		};
		if (options && Object.keys(options).length > 0) {
			payload.options = options;
		}
		lambdaPromises.push([lambda.invoke({
			FunctionName: 'phpstan-runner-prod-main',
			Payload: JSON.stringify(payload),
		}).promise(), phpVersion]);
	}

	const versionedErrors: any[] = [];
	for (const tuple of lambdaPromises) {
		const promise = tuple[0];
		const phpVersion = tuple[1];
		const lambdaResult = await promise;

		const jsonResponse = JSON.parse(lambdaResult.Payload as string);
		const data: any = {
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
		};
		if (typeof jsonResponse.fixedCode !== 'undefined') {
			data.fixedCode = jsonResponse.fixedCode;
		}
		if (typeof jsonResponse.fixedCodeDiff !== 'undefined') {
			data.fixedCodeDiff = jsonResponse.fixedCodeDiff;
		}
		versionedErrors.push(data);
	}

	return versionedErrors;
}

function createTabs(versionedErrors: {phpVersion: number, errors: PHPStanError[], fixedCode?: string, fixedCodeDiff?: string}[]): any[] {
	const versions: {versions: number[], errors: PHPStanError[], fixedCode?: string, fixedCodeDiff?: string}[] = [];
	let last: {versions: number[], errors: PHPStanError[], fixedCode?: string, fixedCodeDiff?: string} | null = null;
	for (const version of versionedErrors) {
		const phpVersion = version.phpVersion;
		const errors = version.errors;
		const current: {versions: number[], errors: PHPStanError[], fixedCode?: string, fixedCodeDiff?: string} = {
			versions: [phpVersion],
			errors,
		};
		if (typeof version.fixedCode !== 'undefined') {
			current.fixedCode = version.fixedCode;
		}
		if (typeof version.fixedCodeDiff !== 'undefined') {
			current.fixedCodeDiff = version.fixedCodeDiff;
		}
		if (last === null) {
			last = current;
			continue;
		}

		if (errors.length !== last.errors.length) {
			versions.push(last);
			last = current;
			continue;
		}

		if (typeof version.fixedCode !== 'undefined') {
			if (typeof last.fixedCode === 'undefined') {
				versions.push(last);
				last = current;
				continue;
			}

			if (version.fixedCode !== last.fixedCode) {
				versions.push(last);
				last = current;
				continue;
			}
		} else if (typeof last.fixedCode !== 'undefined') {
			versions.push(last);
			last = current;
			continue;
		}

		let merge = true;
		for (const i in errors) {
			if (!errors.hasOwnProperty(i)) {
				continue;
			}
			if (!errorsEqual(errors[i], last.errors[i])) {
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

	versions.sort((a, b) => {
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
		const tabData: any = {
			errors: version.errors,
			title: title,
		};
		if (typeof version.fixedCode !== 'undefined') {
			tabData.fixedCode = version.fixedCode;
		}
		if (typeof version.fixedCodeDiff !== 'undefined') {
			tabData.fixedCodeDiff = version.fixedCodeDiff;
		}
		tabs.push(tabData);
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
		const options = validateOptions(json.options);

		const versionedErrors = await analyseResultInternal(
			json.code,
			json.level,
			runStrictRules,
			runBleedingEdge,
			treatPhpDocTypesAsCertain,
			ALL_PHP_VERSIONS,
			options,
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
						options: options,
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
		if (e instanceof z.ZodError) {
			return Promise.resolve({
				statusCode: 400,
				body: JSON.stringify({error: 'Invalid options', details: e.issues}),
			});
		}
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
		const options = validateOptions(json.config?.options);

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
		if (!phpVersionsToAnalyse.includes(80500)) {
			phpVersionsToAnalyse.push(80500);
		}

		const newResult = await analyseResultInternal(
			json.code,
			json.level,
			strictRules,
			bleedingEdge,
			treatPhpDocTypesAsCertain,
			phpVersionsToAnalyse,
			options,
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
				options,
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
						if (!errorsEqual(firstFilteredNewTab.errors[i], firstNewTab.errors[i])) {
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
		const options = validateOptions(json.config?.options);

		const bodyJson: any = {
			code: json.code,
			errors: json.errors,
			version: json.version,
			level: json.level,
			config: {
				strictRules,
				bleedingEdge,
				treatPhpDocTypesAsCertain,
				options,
			},
		};
		if (typeof json.versionedErrors !== 'undefined') {
			bodyJson.versionedErrors = json.versionedErrors;
		} else {
			bodyJson.versionedErrors = [{phpVersion: 70400, errors: json.errors}];
		}
		bodyJson.tabs = createTabs(bodyJson.versionedErrors);
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
			ALL_PHP_VERSIONS,
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

function withCors<T extends HttpRequest>(handler: (request: T) => Promise<HttpResponse>): (event: T) => Promise<HttpResponse & {headers: Record<string, string>}> {
	return async (event) => {
		const result = await handler(event);
		return {
			...result,
			headers: {
				...((result as HttpResponse & {headers?: Record<string, string>}).headers ?? {}),
				'Access-Control-Allow-Origin': '*',
			},
		};
	};
}

module.exports = {
	analyseResult: withCors(analyseResult),
	retrieveResult: withCors(retrieveResult),
	retrieveSample: withCors(retrieveSample),
	retrieveLegacyResult: withCors(retrieveLegacyResult),
};
