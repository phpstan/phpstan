import {test, expect, Page} from '@playwright/test';

// PHPantom runs as wasm in a Web Worker, loaded eagerly when /try opens. The
// first completion has to wait for that ~11 MB module to download + initialise.
const COMPLETION_TIMEOUT = 30_000;

// Replace the playground editor's contents with the given PHP.
async function setCode(page: Page, code: string): Promise<void> {
	const editor = page.locator('.cm-content').first();
	await editor.click();
	await page.keyboard.press('ControlOrMeta+A');
	await page.keyboard.press('Backspace');
	await page.keyboard.insertText(code);
}

function popup(page: Page) {
	return page.locator('.cm-tooltip-autocomplete');
}

async function labels(page: Page): Promise<string[]> {
	return page.locator('.cm-tooltip-autocomplete .cm-completionLabel').allInnerTexts();
}

test.beforeEach(async ({page}) => {
	await page.goto('/try');
	await expect(page.locator('.cm-content').first()).toBeVisible();
});

test('completes instance methods and properties after ->', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class Greeter {',
		'    public function hello(string $name): string { return $name; }',
		'    public string $title = "";',
		'}',
		'$g = new Greeter();',
		'$g->',
	].join('\n'));

	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});

	const items = await labels(page);
	expect(items.some((l) => l.startsWith('hello'))).toBeTruthy();
	expect(items).toContain('title');
});

test('auto-triggers completion while typing a member name', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class Greeter { public function greet(): void {} }',
		'$g = new Greeter();',
		'$g->gr',
	].join('\n'));

	// One more typed character (a real keystroke) triggers completion.
	await page.keyboard.type('e');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	expect((await labels(page)).some((l) => l.startsWith('greet'))).toBeTruthy();
});

test('completes static members after ::', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class Box {',
		'    public static function make(): self { return new self(); }',
		'    const VERSION = 1;',
		'}',
		'Box::',
	].join('\n'));

	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});

	const items = await labels(page);
	expect(items.some((l) => l.startsWith('make'))).toBeTruthy();
	expect(items).toContain('VERSION');
});

test('completes global functions by prefix', async ({page}) => {
	await setCode(page, '<?php\n');
	await page.keyboard.type('str_re');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	expect((await labels(page)).some((l) => l.startsWith('str_re'))).toBeTruthy();
});

test('completes PHPStan stub functions shipped from phpstan-src', async ({page}) => {
	await setCode(page, '<?php\n\\PHPStan\\Testing\\assertT');
	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	expect((await labels(page)).some((l) => l.startsWith('assertType'))).toBeTruthy();
});

test('completes the PHPStan\\dumpType helper', async ({page}) => {
	await setCode(page, '<?php\n\\PHPStan\\dump');
	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	expect((await labels(page)).some((l) => l.startsWith('dumpType'))).toBeTruthy();
});

test('completes static members of the TrinaryLogic stub class', async ({page}) => {
	await setCode(page, '<?php\n\\PHPStan\\TrinaryLogic::');
	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	expect((await labels(page)).some((l) => l.startsWith('createYes'))).toBeTruthy();
});

// The two tests below depend on the patched @codemirror/autocomplete and
// @codemirror/lsp-client (see patches/). Without those patches the parameter
// placeholder isn't selected and the auto-import use statement isn't applied.
test('selects the parameter placeholder after completing a method with args', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class Greeter { public function greet(string $name): void {} }',
		'$g = new Greeter();',
		'$g->gree',
	].join('\n'));

	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	// Wait until the worker has actually surfaced greet before accepting it.
	await expect.poll(() => labels(page), {timeout: COMPLETION_TIMEOUT}).toContain('greet($name)');
	await page.keyboard.press('Enter');

	// The first placeholder ($name) is selected — not the empty $0 — and the
	// LSP escape is unescaped (no leading backslash).
	await expect.poll(() => page.evaluate(() => window.getSelection()!.toString()), {timeout: COMPLETION_TIMEOUT}).toBe('$name');
});

test('adds a use statement when completing a class inside a namespace', async ({page}) => {
	await setCode(page, [
		'<?php',
		'',
		'namespace App;',
		'',
		'DateTimeImmu',
	].join('\n'));

	await page.keyboard.press('Control+Space');
	await expect(popup(page)).toBeVisible({timeout: COMPLETION_TIMEOUT});
	await expect.poll(() => labels(page), {timeout: COMPLETION_TIMEOUT}).toContain('DateTimeImmutable');
	await page.keyboard.press('Enter');

	// additionalTextEdits add the import alongside the inserted name.
	const doc = page.locator('.cm-content').first();
	await expect(doc).toContainText('use DateTimeImmutable;');
});

test('Cmd/Ctrl-click jumps to an in-file declaration', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class Greeter {',
		'    public function greet(): string { return "hi"; }',
		'}',
		'$g = new Greeter();',
		'$g->greet();',
	].join('\n'));
	// Let the worker index the document before resolving the definition.
	await page.waitForTimeout(500);

	// Modifier-click the greet() call (second 'greet' token; the first is the
	// declaration). ControlOrMeta maps to Cmd on macOS, Ctrl elsewhere — the
	// same split the extension uses.
	await page.locator('.cm-content').getByText('greet', {exact: true}).nth(1)
		.click({modifiers: ['ControlOrMeta']});

	// The declaration line flashes (and the cursor jumps there).
	await expect(page.locator('.cm-goto-def-flash'))
		.toContainText('function greet', {timeout: COMPLETION_TIMEOUT});
});
