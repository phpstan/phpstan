---
title: "possiblyImpure.shellExec"
shortDescription: "Pure function executes a shell command using the backtick operator."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function listFiles(): string
{
	return (string) `ls`;
}
```

## Why is it reported?

This identifier is not reported in practice because shell execution with the backtick operator is always considered definitely impure, not just possibly impure. PHPStan reports [`impure.shellExec`](/error-identifiers/impure.shellExec) instead.

A function or method marked as `@phpstan-pure` contains the backtick operator (`` `...` ``), which executes a shell command, the same as calling `shell_exec()`. Pure functions must not have side effects and their return value must depend only on their arguments. Running an external command interacts with the operating system and its output can differ between calls.

## How to fix it

Move the shell execution out of the pure function and pass its result in as an argument:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
-function listFiles(): string
+function formatFileList(string $output): string
 {
-	return (string) `ls`;
+	return trim($output);
 }
+
+$files = formatFileList((string) `ls`);
```

If the function needs to run a shell command, remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function listFiles(): string
 {
 	return (string) `ls`;
 }
```
