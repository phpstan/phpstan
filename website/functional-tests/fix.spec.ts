import {test, expect, Page} from '@playwright/test';

// The Fix button and its dialog: the fixes are shown as a diff, applied to the
// code in the editor, and undone and redone with the editor's history.
//
// The API is mocked. The snippet's code has two fixable errors on the newest PHP
// versions and none on the older ones; the fixed code has one error left.

const SNIPPET_ID = '5b1f0c3e-8a2d-4f6b-9c7e-1d2a3b4c5d6e';

const CODE = `<?php declare(strict_types = 1);

class A
{
	public function foo(): void {}
}

class B extends A
{
	public function foo(): void {}
}

function doFoo(int $i): int
{
	return $i; // @phpstan-ignore argument.type
}
`;

const FIXED_CODE = `<?php declare(strict_types = 1);

class A
{
	public function foo(): void {}
}

class B extends A
{
	#[\\Override]
	public function foo(): void {}
}

function doFoo(int $i): int
{
	return $i;
}
`;

const OVERRIDE_ERROR = 'Method B::foo() overrides method A::foo() but is missing the #[\\Override] attribute.';
const IGNORE_ERROR = 'No error with identifier argument.type is reported on line 15.';
const LEFT_ERROR = 'Some error the fix leaves.';

const config = {strictRules: false, bleedingEdge: false, treatPhpDocTypesAsCertain: true};

const fixableTabs = [
	{
		title: 'PHP 8.3 – 8.5 (2 errors)',
		errors: [
			{line: 10, message: OVERRIDE_ERROR, identifier: 'method.missingOverride', ignorable: true, fixable: true},
			{line: 15, message: IGNORE_ERROR, identifier: 'ignore.unmatchedIdentifier', ignorable: false, fixable: true},
		],
		fixedCode: FIXED_CODE,
	},
	{title: 'PHP 7.2 – 8.2', errors: []},
];

const fixedTabs = [{title: 'PHP 7.2 – 8.5 (1 error)', errors: [{line: 3, message: LEFT_ERROR, ignorable: true}]}];

type Api = {analyses: string[]};

async function mockApi(page: Page): Promise<Api> {
	const api: Api = {analyses: []};
	await page.route('https://api.phpstan.org/**', async (route) => {
		const url = new URL(route.request().url());
		const isSnippet = url.searchParams.get('id') === SNIPPET_ID;
		if (url.pathname === '/sample' && isSnippet) {
			await route.fulfill({json: {code: CODE, level: '10', config, tabs: fixableTabs}});
			return;
		}
		if (url.pathname === '/result' && isSnippet) {
			await route.fulfill({json: {code: CODE, level: '10', config, tabs: fixableTabs, upToDateTabs: fixableTabs}});
			return;
		}
		if (url.pathname === '/analyse') {
			const code = (route.request().postDataJSON() as {code: string}).code;
			api.analyses.push(code);
			await route.fulfill({json: {tabs: code === CODE ? fixableTabs : fixedTabs}});
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

// Replaces the code in the editor, as a paste does.
async function setCode(page: Page, code: string): Promise<void> {
	await page.locator('.cm-content').click();
	await page.keyboard.press('ControlOrMeta+a');
	await page.locator('.cm-content').evaluate((element, text) => {
		const data = new DataTransfer();
		data.setData('text/plain', text);
		element.dispatchEvent(new ClipboardEvent('paste', {clipboardData: data, bubbles: true, cancelable: true}));
	}, code);
}

async function expectCode(page: Page, code: string): Promise<void> {
	await expect.poll(async () => (await page.locator('#playground-editor .cm-line').allTextContents()).join('\n')).toBe(code);
}

async function undo(page: Page): Promise<void> {
	await page.keyboard.press('ControlOrMeta+z');
}

// The shortcut people use on every platform; CodeMirror alone binds it only on macOS.
async function redo(page: Page): Promise<void> {
	await page.keyboard.press('ControlOrMeta+Shift+KeyZ');
}

const fixButton = (page: Page) => page.getByRole('button', {name: 'Fix 2 errors'});
const dialog = (page: Page) => page.locator('#fixDialog');
const tab = (page: Page, title: string) => page.getByRole('link', {name: title, exact: true});

async function applyFix(page: Page): Promise<void> {
	await fixButton(page).click();
	await expect(dialog(page)).toBeVisible();
	await dialog(page).getByRole('button', {name: 'Apply'}).click();
	await expect(dialog(page)).toBeHidden();
}

// The shown result: the errors in the table, or "No errors!", and the same lines marked in the editor.
async function expectErrors(page: Page, errors: {line: number; message: string}[]): Promise<void> {
	const table = page.locator('table');
	if (errors.length === 0) {
		await expect(page.getByText('No errors!')).toBeVisible();
		await expect(table).toBeHidden();
	} else {
		await expect(page.getByText('No errors!')).toBeHidden();
		await expect(table.locator('tbody tr')).toHaveCount(errors.length);
		for (const error of errors) {
			await expect(table.getByText(error.message, {exact: true})).toBeVisible();
		}
	}

	const lines = [...new Set(errors.map((error) => error.line))].sort((a, b) => a - b);
	await expect.poll(() => page.locator('#playground-editor .cm-line').evaluateAll(
		(elements) => elements.flatMap((element, i) => element.classList.contains('bg-red-200/50') ? [i + 1] : []),
	)).toEqual(lines);
}

const FIXABLE_ERRORS = [{line: 10, message: OVERRIDE_ERROR}, {line: 15, message: IGNORE_ERROR}];
const LEFT_ERRORS = [{line: 3, message: LEFT_ERROR}];

test('fixable errors have a Fixable pill and the Fix button counts them', async ({page}) => {
	await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await expectErrors(page, FIXABLE_ERRORS);
	await expect(page.locator('table').getByText('Fixable')).toHaveCount(2);
	await expect(fixButton(page)).toBeVisible();

	// The older PHP versions have nothing to fix.
	await tab(page, 'PHP 7.2 – 8.2').click();
	await expectErrors(page, []);
	await expect(fixButton(page)).toBeHidden();
});

test('the dialog shows the diff, Cancel, the close button and Esc leave the code alone', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await fixButton(page).click();
	await expect(dialog(page)).toBeVisible();
	await expect(dialog(page).locator('.cm-changedLine')).toContainText(['#[\\Override]']);
	await expect(dialog(page).locator('.cm-deletedLine')).toContainText(['// @phpstan-ignore argument.type']);
	await dialog(page).getByRole('button', {name: 'Cancel'}).click();
	await expect(dialog(page)).toBeHidden();

	await fixButton(page).click();
	await dialog(page).getByRole('button', {name: 'Close'}).click();
	await expect(dialog(page)).toBeHidden();

	await fixButton(page).click();
	await page.keyboard.press('Escape');
	await expect(dialog(page)).toBeHidden();

	await expectCode(page, CODE);
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	expect(api.analyses).toHaveLength(0);
});

test('applying the fixes edits the code and analyses it, undo and redo move between them', async ({page}) => {
	await mockApi(page);
	await open(page, '/try');
	await setCode(page, CODE);
	await expectErrors(page, FIXABLE_ERRORS);

	await applyFix(page);
	await expectCode(page, FIXED_CODE);
	await expectErrors(page, LEFT_ERRORS);
	await expect(fixButton(page)).toBeHidden();

	// One undo takes back the whole fix.
	await page.locator('.cm-content').click();
	await undo(page);
	await expectCode(page, CODE);
	await expectErrors(page, FIXABLE_ERRORS);
	await expect(fixButton(page)).toBeVisible();

	await redo(page);
	await expectCode(page, FIXED_CODE);
	await expectErrors(page, LEFT_ERRORS);

	await undo(page);
	await expectCode(page, CODE);
	await expectErrors(page, FIXABLE_ERRORS);
});

test('undo after applying the fixes to a snippet brings back its URL and its result', async ({page}) => {
	const api = await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);
	await expectErrors(page, FIXABLE_ERRORS);

	await applyFix(page);
	await expect(page).toHaveURL('/try');
	await expectErrors(page, LEFT_ERRORS);

	await page.locator('.cm-content').click();
	await undo(page);
	await expectCode(page, CODE);
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expectErrors(page, FIXABLE_ERRORS);

	await redo(page);
	await expectCode(page, FIXED_CODE);
	await expect(page).toHaveURL('/try');
	await expectErrors(page, LEFT_ERRORS);

	await undo(page);
	await expect(page).toHaveURL(`/r/${SNIPPET_ID}`);
	await expectErrors(page, FIXABLE_ERRORS);

	// The fixes can be applied again.
	await applyFix(page);
	await expectErrors(page, LEFT_ERRORS);
	expect(api.analyses).toEqual([FIXED_CODE, FIXED_CODE, FIXED_CODE]);
});

test('the highlighted tab is the one whose result is shown, also after a reload and going back', async ({page}) => {
	await mockApi(page);
	await open(page, `/r/${SNIPPET_ID}`);

	await tab(page, 'PHP 7.2 – 8.2').click();
	await expectErrors(page, []);
	await expect(tab(page, 'PHP 7.2 – 8.2')).toHaveClass(/cursor-default/);
	await expect(tab(page, 'PHP 8.3 – 8.5 (2 errors)')).not.toHaveClass(/cursor-default/);

	await page.reload();
	await expect(page.locator('.cm-content')).toBeVisible();
	await expectErrors(page, []);
	await expect(tab(page, 'PHP 7.2 – 8.2')).toHaveClass(/cursor-default/);
	await expect(tab(page, 'PHP 8.3 – 8.5 (2 errors)')).not.toHaveClass(/cursor-default/);

	await page.getByRole('link', {name: 'Learn more about PHPStan »'}).click();
	await expect(page).toHaveURL('/');
	await page.goBack();
	await expect(page.locator('.cm-content')).toBeVisible();
	await expectErrors(page, []);
	await expect(tab(page, 'PHP 7.2 – 8.2')).toHaveClass(/cursor-default/);

	await tab(page, 'PHP 8.3 – 8.5 (2 errors)').click();
	await expectErrors(page, FIXABLE_ERRORS);
	await expect(tab(page, 'PHP 8.3 – 8.5 (2 errors)')).toHaveClass(/cursor-default/);
	await expect(tab(page, 'PHP 7.2 – 8.2')).not.toHaveClass(/cursor-default/);
});
