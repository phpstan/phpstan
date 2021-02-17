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

const lambda = new Lambda();
const s3 = new S3();

async function analyseResultInternal(
	code: string,
	level: string,
	runStrictRules: boolean,
	runBleedingEdge: boolean,
	treatPhpDocTypesAsCertain: boolean,
): Promise<any[]> {
	const lambdaPromises: [Promise<PromiseResult<Lambda.InvocationResponse, AWSError>>, number][] = [];
	for (const phpVersion of [70100, 70200, 70300, 70400, 80000]) {
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
			errors: jsonResponse.result.map((error: any) => {
				return {
					line: error.line,
					message: error.message,
				};
			}),
		});
	}

	return versionedErrors;
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
			treatPhpDocTypesAsCertain
		);

		const response: any = {
			errors: versionedErrors[versionedErrors.length - 1].errors,
			versionedErrors: versionedErrors,
		};

		if (saveResult) {
			const id: string = uuid() as string;
			await s3.putObject({
				Bucket: 'phpstan-playground',
				Key: 'api/results/' + id + '.json',
				ContentType: 'application/json',
				Body: JSON.stringify({
					code: json.code,
					errors: response.errors,
					versionedErrors: response.versionedErrors,
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
		const bodyJson: any = {
			code: json.code,
			errors: json.errors,
			version: json.version,
			level: json.level,
			config: {
				strictRules: typeof json.config.strictRules !== 'undefined' ? json.config.strictRules : false,
				bleedingEdge: typeof json.config.bleedingEdge !== 'undefined' ? json.config.bleedingEdge : false,
				treatPhpDocTypesAsCertain: typeof json.config.treatPhpDocTypesAsCertain !== 'undefined' ? json.config.treatPhpDocTypesAsCertain : true,
			},
		};
		if (typeof json.versionedErrors !== 'undefined') {
			bodyJson.versionedErrors = json.versionedErrors;
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
		return Promise.resolve({
			statusCode: 200,
			body: JSON.stringify({
				code: inputJson.phpCode,
				htmlErrors: convert.toHtml(JSON.parse(outputObject.Body as string).output),
				upToDateErrors: await analyseResultInternal(
					inputJson.phpCode,
					inputJson.level.toString(),
					false,
					false,
					true,
				),
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
	retrieveLegacyResult: middy(retrieveLegacyResult).use(corsMiddleware),
};
