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
  bash: ["*"]
  github:
    toolsets: [default, repos]
  web-fetch:

safe-outputs:
  github-token: ${{ secrets.PHPSTAN_BOT_TOKEN }}
  noop:

steps:
  - uses: actions/checkout@v4
    with:
      token: ${{ secrets.PHPSTAN_BOT_TOKEN }}
  - uses: actions/checkout@v4
    with:
      path: __push-workspace
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

### 3b. Understand the identifier prefix when source uses `$location->createIdentifier()`

When reading the rule source code in step 3a, check whether the linked source code line uses `$location->createIdentifier()`. If it does, the identifier prefix comes from `ClassNameUsageLocation` in phpstan-src, and the prefix indicates a specific PHP language feature — which may not be obvious from the prefix name alone.

If the source code does **not** use `$location->createIdentifier()`, the prefix is set directly by the rule and typically describes its PHP feature straightforwardly.

Consult the "Identifier prefix reference" section in `website/errors/CLAUDE.md` for the complete prefix-to-PHP-feature mapping tables.

### 3c. Find test fixtures with code examples

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

### 3d. Determine if the error is ignorable

An error identifier is **not ignorable** if:
- The source code uses `->nonIgnorable()` in the error builder chain
- The identifier starts with `phpstan.` (internal PHPStan errors)
- The identifier starts with `phpstanPlayground.` (playground-specific)

All other identifiers are ignorable.

### 3e. Check for configuration options

Some rules accept constructor parameters from PHPStan configuration. Look at the rule class constructor for injected config values. Cross-reference with `website/src/config-reference.md` to find the documented parameter name.

Examples of configurable rules:
- Rules that check strict types may be controlled by `treatPhpDocTypesAsCertain`
- Dead code rules may be controlled by `checkAlwaysTrueCheckTypeFunctionCall`
- Some rules are only active at certain PHPStan levels

## Step 4: Generate markdown files

Create `website/errors/` directory if it doesn't exist.

For each identifier, create `website/errors/<identifier>.md` following the file format, content guidelines, and tone described in `website/errors/CLAUDE.md`. Read that file before generating any markdown.

## Step 5: Commit changes and create pull request

After generating all markdown files, push the changes and create a draft PR:

```bash
cp -r website/errors/ __push-workspace/website/errors/
cd __push-workspace
git config user.name "phpstan-bot"
git config user.email "ondrej+phpstanbot@mirtes.cz"
git checkout -b document-error-identifiers-batch-$(head -c 8 /dev/urandom | xxd -p)
git add website/errors/
git commit -m "Document error identifiers"
git push origin HEAD
gh pr create --base 2.2.x --draft --title "[Docs] Document error identifiers" --body "PR DESCRIPTION HERE"
```

Replace `PR DESCRIPTION HERE` with a description listing which identifiers were documented with a one-line summary of each.

An example output is available in `website/errors/CLAUDE.md`.
