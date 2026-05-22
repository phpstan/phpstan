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
	// Click the option rather than pressing Enter — Enter accepts whichever item
	// happens to be highlighted, which races with the worker on slow CI.
	await page.locator('.cm-tooltip-autocomplete li').filter({hasText: 'greet'})
		.first().click({timeout: COMPLETION_TIMEOUT});

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
	await page.locator('.cm-tooltip-autocomplete li').filter({hasText: 'DateTimeImmutable'})
		.first().click({timeout: COMPLETION_TIMEOUT});

	// additionalTextEdits add the import alongside the inserted name.
	const doc = page.locator('.cm-content').first();
	await expect(doc).toContainText('use DateTimeImmutable;', {timeout: COMPLETION_TIMEOUT});
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

test('highlights every occurrence of the symbol under the cursor', async ({page}) => {
	await setCode(page, [
		'<?php',
		'function sum($total) {',
		'    return $total + $total;',
		'}',
	].join('\n'));
	await page.waitForTimeout(500);

	await page.locator('.cm-content').getByText('$total', {exact: true}).first().click();
	// The param declaration + both uses.
	await expect.poll(() => page.locator('.cm-occurrence').count(), {timeout: COMPLETION_TIMEOUT}).toBe(3);
});

test('Ctrl-R inline-renames a variable across its scope, keeping the $', async ({page}) => {
	await setCode(page, [
		'<?php',
		'function sum($total) {',
		'    return $total + $total;',
		'}',
	].join('\n'));
	await page.waitForTimeout(500);

	await page.locator('.cm-content').getByText('$total', {exact: true}).first().click();
	await page.keyboard.press('Control+r');
	// All three occurrences become selections you edit at once.
	await expect.poll(() => page.locator('.cm-selectionBackground').count(), {timeout: COMPLETION_TIMEOUT}).toBe(3);
	await page.keyboard.type('amount');

	const doc = page.locator('.cm-content').first();
	await expect(doc).toContainText('function sum($amount)');
	await expect(doc).toContainText('return $amount + $amount;');
});

test('Ctrl-R rename of a method is type-aware (same-named method on another class untouched)', async ({page}) => {
	await setCode(page, [
		'<?php',
		'class A { public function run(): int { return 1; } }',
		'class B { public function run(): int { return 2; } }',
		'$a = new A(); echo $a->run();',
	].join('\n'));
	await page.waitForTimeout(500);

	// Rename from the $a->run() call: only A::run (declaration + this call).
	await page.locator('.cm-content').getByText('run', {exact: true}).last().click();
	await page.keyboard.press('Control+r');
	await expect.poll(() => page.locator('.cm-selectionBackground').count(), {timeout: COMPLETION_TIMEOUT}).toBe(2);
	await page.keyboard.type('execute');

	const doc = page.locator('.cm-content').first();
	await expect(doc).toContainText('class A { public function execute()');
	await expect(doc).toContainText('$a->execute();');
	await expect(doc).toContainText('class B { public function run()'); // untouched
});
