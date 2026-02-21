---
name: analyse-with-phpstan
description: Analyse PHP code with PHPStan via the playground API. Tests across all PHP versions (7.2–8.5) and reports errors grouped by version. Supports configuring level, strict rules, and bleeding edge.
argument-hint: <php-code-or-file>
disable-model-invocation: false
---

# Analyse PHP code with PHPStan

Analyse PHP code using the PHPStan playground API at `https://api.phpstan.org/analyse`. This runs PHPStan across PHP versions 7.2–8.5 and returns errors for each version.

The code to analyse: `$ARGUMENTS`

## Step 1: Prepare the code

Get the PHP code to analyse. If `$ARGUMENTS` is a file path, read the file contents. The code must start with `<?php`.

## Step 2: Determine settings

Unless the user specified otherwise, use these defaults:
- **level**: `"10"` (strictest)
- **strictRules**: `false`
- **bleedingEdge**: `false`
- **treatPhpDocTypesAsCertain**: `true`

If the user asked for strict rules or bleeding edge, set those to `true`.

## Step 3: Call the playground API

Submit the code via POST:

```bash
curl -s -X POST 'https://api.phpstan.org/analyse' \
  -H 'Content-Type: application/json' \
  -d '{
    "code": "<PHP code, JSON-escaped>",
    "level": "<level>",
    "strictRules": <true|false>,
    "bleedingEdge": <true|false>,
    "treatPhpDocTypesAsCertain": <true|false>,
    "saveResult": true
  }'
```

The code value must be properly JSON-escaped (escape quotes, backslashes, newlines).

## Step 4: Parse the response

The response JSON contains:
- `versionedErrors` — array of objects, one per PHP version, each with:
  - `phpVersion` — integer encoding: e.g. `80400` = PHP 8.4, `70400` = PHP 7.4
  - `errors` — array of error objects with `message`, `line`, `identifier`, `tip` (optional), `ignorable`
- `id` — UUID for the saved result

Convert `phpVersion` integers to readable strings: `Math.floor(v / 10000)` `.` `Math.floor((v % 10000) / 100)`.

## Step 5: Present results as markdown

Output the results in this format:

### Playground link

`https://phpstan.org/r/<id>`

### Settings used

**Level:** `<level>` | **Strict rules:** yes/no | **Bleeding edge:** yes/no

### Errors

Group consecutive PHP versions that have identical errors (same messages, lines, and identifiers) into ranges. For example, if PHP 7.2–8.3 all report the same errors, show them as one group.

If all PHP versions report identical errors, show a single group:

**All PHP versions (no differences):**

| Line | Error | Identifier |
|------|-------|------------|
| 10 | `Parameter #1 $foo expects string, int given.` | `argument.type` |

If errors differ across versions, show separate groups:

**PHP 8.0 – 8.5:**

| Line | Error | Identifier |
|------|-------|------------|
| 10 | `Parameter #1 $foo expects string, int given.` | `argument.type` |

**PHP 7.2 – 7.4:**

No errors.

If there are no errors on any PHP version, say: **No errors found on any PHP version.**
