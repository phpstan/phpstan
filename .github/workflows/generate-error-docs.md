---
description: >
  Generates documentation markdown files for PHPStan error identifiers by
  reading rule source code and tests from PHPStan repositories.

on:
  workflow_dispatch:

permissions: read-all

engine:
  id: claude
  model: claude-opus-4-6
  env:
    CLAUDE_CODE_OAUTH_TOKEN: ${{ secrets.CLAUDE_CODE_OAUTH_TOKEN }}

timeout-minutes: 120

tools:
  github:
    toolsets: [default, repos]
  web-fetch:

safe-outputs:
  github-token: ${{ secrets.PHPSTAN_BOT_TOKEN }}
  create-pull-request:
    draft: true
    base-branch: 2.2.x
    title-prefix: "[Docs] "
  noop:

steps:
  - uses: actions/checkout@v4
    with:
      token: ${{ secrets.PHPSTAN_BOT_TOKEN }}
---

# Generate Error Identifier Documentation

You are generating documentation pages for PHPStan error identifiers. PHPStan is a PHP static analysis tool that finds bugs in code without running it. Each error identifier (like `argument.type`, `deadCode.unreachable`, `property.notFound`) categorizes a specific type of error.

The goal is to create a markdown file for each identifier in `website/errors/` explaining what the error means, showing a code example, and offering ways to fix it. Each run picks 100 random undocumented identifiers so the workflow can be dispatched repeatedly until all are covered.

## Step 1: Pick 100 undocumented identifiers

Read `website/src/errorsIdentifiers.json`. This JSON maps each identifier to its rule classes and source code locations:

```json
{
  "argument.type": {
    "PHPStan\\Rules\\Functions\\CallToFunctionParametersRule": {
      "phpstan/phpstan-src": [
        "https://github.com/phpstan/phpstan-src/blob/2.2.x/src/Rules/FunctionCallParametersCheck.php#L280"
      ]
    }
  }
}
```

Then list existing files in `website/errors/`. Each file is named `<identifier>.md`. Any identifier that already has a file is already documented — skip it.

From the remaining undocumented identifiers, pick 100 at random. If fewer than 100 are left, process all of them.

If all identifiers are already documented, call the `noop` safe output with a message explaining that all identifiers are already documented, and stop.

## Step 2: Clone required repositories

Clone only the repositories referenced by the selected 100 identifiers. Extract the branch name from the GitHub URLs (e.g., `blob/2.2.x/` → branch `2.2.x`).

Use shallow clones to save time:

```bash
git clone --depth 1 --branch <branch> https://github.com/phpstan/<repo>.git /tmp/repos/<repo>
```

The possible repositories are:
- `phpstan/phpstan-src` (typically branch `2.2.x`)
- `phpstan/phpstan-strict-rules`
- `phpstan/phpstan-deprecation-rules`
- `phpstan/phpstan-doctrine`
- `phpstan/phpstan-symfony`
- `phpstan/phpstan-phpunit`
- `phpstan/phpstan-nette`

## Step 3: Research each identifier

For each identifier, gather the information needed to write the documentation.

### 3a. Read the rule source code

From the JSON URLs, extract the file path and line number. Read the source code around those lines to find:

1. **Error message**: Look for `RuleErrorBuilder::message('...')` — this is the exact error text PHPStan shows
2. **Trigger condition**: Read the surrounding `processNode()` method to understand what code pattern causes this error
3. **Tips**: Look for `->tip('...')` or `->addTip('...')` calls in the same builder chain — these often contain links to blog posts or documentation pages on phpstan.org
4. **Non-ignorable**: Check for `->nonIgnorable()` in the builder chain

### 3b. Find test fixtures with code examples

For a rule class like `PHPStan\Rules\Functions\CallToFunctionParametersRule`:
- Test class: `tests/PHPStan/Rules/Functions/CallToFunctionParametersRuleTest.php`
- Test data: `tests/PHPStan/Rules/Functions/data/*.php`

For extension repos (phpstan-doctrine, phpstan-symfony, etc.), the path pattern may differ — check `tests/Rules/` or `tests/` directories.

Read the test class to find which data files trigger this specific identifier. Look for the error message text in the test assertions:

```php
$this->analyse([__DIR__ . '/data/someFile.php'], [
    ['Error message text', 42],
]);
```

Then read the corresponding data file to extract a minimal code example.

### 3c. Determine if the error is ignorable

An error identifier is **not ignorable** if:
- The source code uses `->nonIgnorable()` in the error builder chain
- The identifier starts with `phpstan.` (internal PHPStan errors)
- The identifier starts with `phpstanPlayground.` (playground-specific)

All other identifiers are ignorable.

### 3d. Check for configuration options

Some rules accept constructor parameters from PHPStan configuration. Look at the rule class constructor for injected config values. Cross-reference with `website/src/config-reference.md` to find the documented parameter name.

Examples of configurable rules:
- Rules that check strict types may be controlled by `treatPhpDocTypesAsCertain`
- Dead code rules may be controlled by `checkAlwaysTrueCheckTypeFunctionCall`
- Some rules are only active at certain PHPStan levels

## Step 4: Generate markdown files

Create `website/errors/` directory if it doesn't exist.

For each identifier, create `website/errors/<identifier>.md` with this exact format:

```markdown
---
title: "<identifier>"
ignorable: true
---

## Code example

` ``php
<?php declare(strict_types = 1);

// Minimal PHP code that triggers this error
` ``

## Why is it reported?

Explanation from PHP language perspective.

## How to fix it

Ways to fix the error.
```

### Content guidelines

**Code example section:**
- Use real code from test fixtures when possible
- Keep it minimal — remove unrelated classes, simplify names
- The example must be valid PHP that would actually trigger the identifier
- Start with `<?php declare(strict_types = 1);`
- Use `php` language tag for the code block

**"Why is it reported?" section:**
- Explain the PHP language semantics, not PHPStan internals
- PHPStan points to code that causes crashes, doesn't execute at all, or doesn't do what the developer probably intended
- Be concise and technically precise
- List multiple reasons if applicable
- If the rule's `->tip()` links to a blog post on phpstan.org, mention it: `Learn more: [Blog post title](/blog/post-slug)`

**"How to fix it" section:**
- Offer multiple ways to fix the error when applicable
- Prefer these approaches in order:
  1. Fix the actual bug if it is clearly wrong code
  2. Narrow the type using native PHP type declarations (parameter types, return types)
  3. Narrow the type using PHPDoc types (`@param`, `@return`, `@var` on properties)
  4. Use [type narrowing](/writing-php-code/narrowing-types) in the function body
  5. Configure PHPStan if the rule is configurable
- When the error involves a PHP language feature only available in newer PHP versions, mention the PHPDoc-based alternative that works on older versions too. For example: native return type `never` (PHP 8.1+) can be replaced with `@return never`, native union types (PHP 8.0+) can be expressed as PHPDoc union types, native intersection types (PHP 8.1+) can be expressed as PHPDoc intersection types, standalone types like `true`/`false`/`null` (PHP 8.2+) can be written in PHPDoc. Link to [PHPDoc Basics](/writing-php-code/phpdocs-basics) and [PHPDoc Types](/writing-php-code/phpdoc-types) where relevant.
- Every time a configuration parameter is mentioned, link it to the correct documentation page. Consult `website/src/config-reference.md` to find the right anchor — parameters that have their own `###` heading (like `phpVersion`) link to `/config-reference#phpversion`. Parameters that only appear as "Related config keys" link to the user guide page referenced there (e.g., `reportUnmatchedIgnoredErrors` links to `/user-guide/ignoring-errors#reporting-unused-ignores`, `scanFiles` links to `/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies`).
- Show code fixes. Use `diff-php` syntax when showing changes:

````markdown
```diff-php
- $value = $this->getValue();
+ $value = (string) $this->getValue();
```
````

**Do NOT:**
- Suggest using `assert()` for type narrowing
- Suggest throwing an exception to narrow types
- Suggest using inline `@var` PHPDoc tag
- Suggest ignoring the error (the existing detail page already covers that)
- Use emojis
- Use first person

**Tone:**
- Concise, technically precise, no filler words
- Match the existing phpstan.org documentation style
- Direct and practical

**For extension-specific identifiers** (phpstan-doctrine, phpstan-symfony, etc.):
- Mention which extension package provides the rule (e.g., "This error is reported by `phpstan/phpstan-doctrine`.")

## Step 5: Commit changes and create pull request

After generating all markdown files, commit them locally:

```bash
git config user.name "phpstan-bot"
git config user.email "ondrej+phpstanbot@mirtes.cz"
git add website/errors/
git commit -m "Document error identifiers"
```

Then use the `create-pull-request` safe output to create a draft PR. Set the title to "Document error identifiers" and include a descriptive body listing which identifiers were documented.

## Example output

For `website/errors/deadCode.unreachable.md`:

```markdown
---
title: "deadCode.unreachable"
ignorable: true
---

## Code example

` ``php
<?php declare(strict_types = 1);

function doFoo(): int
{
	return 1;
	echo 'unreachable';
}
` ``

## Why is it reported?

The statement after `return` can never be executed. The `return` statement unconditionally transfers control out of the function, making any code following it in the same block dead code. This usually indicates a logic error or leftover code from refactoring.

## How to fix it

Remove the unreachable code:

` ``diff-php
 function doFoo(): int
 {
 	return 1;
-	echo 'unreachable';
 }
` ``

If the code should execute, restructure the logic so it runs before the return:

` ``diff-php
 function doFoo(): int
 {
+	echo 'this should run';
 	return 1;
-	echo 'unreachable';
 }
` ``
```
