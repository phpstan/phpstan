import {test, expect, Page} from '@playwright/test';
import {readFileSync} from 'node:fs';
import path from 'node:path';

// The playground's URL and browser history: a loaded or shared snippet keeps
// its /r/… URL only while the code and the settings are the ones it was saved
// with, and leaving the page and going back restores what was there.
//
// The API is mocked. The snippets are real ones from phpstan/phpstan issues
// (#15608 and #15604), recorded from https://api.phpstan.org/sample and /result.

type Snippet = {
	sample: {code: string; level: string};
	result: {upToDateTabs: unknown[]};
};

const SNIPPET_ID = '277f76ee-e975-4b5a-87aa-5b4d0c2c2765';
const OTHER_SNIPPET_ID = '3cd4c73b-fd89-4bd1-9220-63ab82df2522';
const SHARED_ID = '0b9a2f4e-5c1d-4e8f-9a7b-6c5d4e3f2a1b';

const snippets: Record<string, Snippet> = Object.fromEntries([SNIPPET_ID, OTHER_SNIPPET_ID].map((id) => [
	id,
	JSON.parse(readFileSync(path.join(__dirname, 'fixtures', `${id}.json`), 'utf8')) as Snippet,
]));

// Shown for the snippet as it was saved.
const SNIPPET_ERROR = 'Coalesce operator ?? is unnecessary because the left side is always set and the right side is null.';
const OTHER_SNIPPET_ERROR = 'If condition is always true.';

test.use({permissions: ['clipboard-read', 'clipboard-write']});

type Api = {
	// The bodies of the POST /analyse requests, in order.
	analyses: {code: string; level: string; saveResult: boolean}[];
	// Snippet ids that GET /sample was asked for.
	samples: string[];
	// Replaces the stored result's up-to-date tabs, to make it out of date.
	upToDateTabs?: unknown[];
};

// The analysis reports one error naming the request, so each result can be told apart.
async function mockApi(page: Page): Promise<Api> {
	const api: Api = {analyses: [], samples: []};
	await page.route('https://api.phpstan.org/**', async (route) => {
		const url = new URL(route.request().url());
		const id = url.searchParams.get('id') ?? '';
		if (url.pathname === '/sample' && id in snippets) {
			api.samples.push(id);
			await route.fulfill({json: snippets[id].sample});
			return;
		}
		if (url.pathname === '/result' && id in snippets) {
			const result = snippets[id].result;
			await route.fulfill({json: api.upToDateTabs ? {...result, upToDateTabs: api.upToDateTabs} : result});
			return;
		}
		if (url.pathname === '/analyse') {
			const body = route.request().postDataJSON() as Api['analyses'][number];
			api.analyses.push(body);
			const message = `Analysis #${api.analyses.length} at level ${body.level}`;
			await route.fulfill({json: {
				tabs: [{title: 'PHP 7.2 – 8.6 (1 error)', errors: [{message, line: 1, ignorable: true}]}],
				...(body.saveResult ? {id: SHARED_ID} : {}),
			}});
			return;
		}
		await route.fulfill({status: 404});
	});

	return api;
}

async function open(page: Page, url: string): Promise<void> {
	await page.goto(url);
	await expect(page.locator('.cm-content')).toBeVisible();
}

async function typeAtEnd(page: Page, text: string): Promise<void> {
	await page.locator('.cm-content').click();
	await page.keyboard.press('ControlOrMeta+End');
	await page.keyboard.type(text);
}

async function undo(page: Page): Promise<void> {
	await page.keyboard.press('ControlOrMeta+z');
}

async function redo(page: Page): Promise<void> {
	await page.keyboard.press('ControlOrMeta+Shift+z');
}

// Leaves the playground through a link and comes back with the browser's back button.
async function leaveAndGoBack(page: Page): Promise<void> {
	await page.getByRole('link', {name: 'Learn more about PHPStan »'}).click();
	await expect(page).toHaveURL('/');
	await page.goBack();
	await expect(page.locator('.cm-content')).toBeVisible();
}

const lastLine = (page: Page) => page.locator('.cm-line').last();

test('a shared snippet shows its stored result without analysing it', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();
	await expect(page.getByText('Found 3 errors')).toBeVisible();
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(page.locator('.cm-content')).toContainText('function getIntOrNullCoalesce(?int $i) {');
	expect(api.analyses).toHaveLength(0);
});

test('editing a snippet leaves its URL, undo and redo move between them', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await typeAtEnd(page, 'x');
	await expect(page).toHaveURL('/try');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();

	await undo(page);
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(lastLine(page)).toHaveText('');
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();

	await redo(page);
	await expect(page).toHaveURL('/try');
	await expect(lastLine(page)).toHaveText('x');
	await expect(page.getByText('Analysis #2 at level 10')).toBeVisible();

	// Restoring the snippet does not analyse it again.
	await undo(page);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();
	await page.waitForTimeout(1000);
	expect(api.analyses).toHaveLength(2);
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
});

test('deleting what was typed brings the snippet URL back', async ({page}) => {
	await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await typeAtEnd(page, 'x');
	await expect(page).toHaveURL('/try');
	await page.keyboard.press('Backspace');
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();
});

test('undo does not bring back the snippet URL when the level is no longer the snippet\'s', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await typeAtEnd(page, 'x');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();
	await page.getByRole('combobox').selectOption('5');
	await expect(page.getByText('Analysis #2 at level 5')).toBeVisible();

	await page.locator('.cm-content').click();
	await undo(page);
	await expect(lastLine(page)).toHaveText('');
	await expect(page.getByText('Analysis #3 at level 5')).toBeVisible();
	await expect(page).toHaveURL('/try');
	expect(api.analyses[2].code).toBe(snippets[SNIPPET_ID].sample.code);

	// The snippet's code and level again.
	await page.getByRole('combobox').selectOption('10');
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();
});

test('after sharing, undo goes back to the shared code and then to what came before', async ({page}) => {
	const api = await mockApi(page);
	await open(page, '/try');

	await typeAtEnd(page, '// a');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();
	await page.getByText('Share', {exact: true}).click();
	await expect(page).toHaveURL(`/r/${SHARED_ID}`);
	expect(api.analyses[1].saveResult).toBe(true);

	await typeAtEnd(page, 'b');
	await expect(page).toHaveURL('/try');

	await undo(page);
	await expect(lastLine(page)).toHaveText('// a');
	await expect(page).toHaveURL(`/r/${SHARED_ID}`);

	// The next undo removes the comment, it is not spent on the sharing.
	await undo(page);
	await expect(lastLine(page)).toHaveText('');
	await expect(page).toHaveURL('/try');

	await redo(page);
	await expect(lastLine(page)).toHaveText('// a');
	await expect(page).toHaveURL(`/r/${SHARED_ID}`);
});

test('going back to an edited snippet restores the code, the result and the undo history', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await typeAtEnd(page, 'x');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();

	await leaveAndGoBack(page);
	await expect(page).toHaveURL('/try');
	await expect(lastLine(page)).toHaveText('x');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();

	await page.locator('.cm-content').click();
	await undo(page);
	await expect(lastLine(page)).toHaveText('');
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();

	await page.goForward();
	await expect(page).toHaveURL('/');
	await page.goBack();
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(lastLine(page)).toHaveText('');
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();

	expect(api.samples).toEqual([SNIPPET_ID]);
	expect(api.analyses).toHaveLength(1);
});

test('code typed right before leaving is still there when going back', async ({page}) => {
	const api = await mockApi(page);
	await open(page, '/try');

	// Leaves before the analysis starts.
	await typeAtEnd(page, '// typed');
	await leaveAndGoBack(page);

	await expect(page).toHaveURL('/try');
	await expect(lastLine(page)).toHaveText('// typed');
	await expect(page.getByText('Analysis #1 at level 10')).toBeVisible();
	expect(api.analyses[0].code).toContain('// typed');
});

test('back and forward move between two snippets without loading them again', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();
	await open(page, `/r/${OTHER_SNIPPET_ID}`);
	await expect(page.getByText(OTHER_SNIPPET_ERROR)).toBeVisible();

	await page.goBack();
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();

	await page.goForward();
	await expect(page).toHaveURL(`/r/${OTHER_SNIPPET_ID}`);
	await expect(page.getByText(OTHER_SNIPPET_ERROR)).toBeVisible();

	expect(api.samples).toEqual([SNIPPET_ID, OTHER_SNIPPET_ID]);
	expect(api.analyses).toHaveLength(0);
});

test('an out-of-date snippet offers the latest result, which has no URL of its own', async ({page}) => {
	const api = await mockApi(page);
	api.upToDateTabs = [{title: 'PHP 7.2 – 8.6 (1 error)', errors: [{message: 'The latest result.', line: 1}]}];
	await open(page, `/r/${SNIPPET_ID}`);

	await expect(page.getByText('This result is out of date.')).toBeVisible();
	await expect(page.getByText(SNIPPET_ERROR)).toBeVisible();

	await page.getByText('Show the latest result').click();
	await expect(page.getByText('The latest result.')).toBeVisible();
	await expect(page).toHaveURL('/try');

	await leaveAndGoBack(page);
	await expect(page).toHaveURL('/try');
	await expect(page.getByText('The latest result.')).toBeVisible();
	expect(api.analyses).toHaveLength(0);
});
