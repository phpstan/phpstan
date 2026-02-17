---
title: "ignore.unmatchedIdentifier"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function add(int $a, int $b): int
{
	return $a + $b; // @phpstan-ignore return.type
}
```

## Why is it reported?

An `@phpstan-ignore` comment specifies an error identifier, but no error with that identifier is actually reported on the given line. This means the ignore directive is unnecessary -- either the error was already fixed, the code was changed, or the identifier was specified incorrectly.

This error is reported when the [`reportUnmatchedIgnoredErrors`](/user-guide/ignoring-errors#reporting-unused-ignores) parameter is enabled (which is the default).

## How to fix it

Remove the unnecessary ignore comment if the error no longer occurs:

```diff-php
 <?php declare(strict_types = 1);

 function add(int $a, int $b): int
 {
-	return $a + $b; // @phpstan-ignore return.type
+	return $a + $b;
 }
```

If the error should still be ignored but the identifier is wrong, correct it to match the actual error identifier. You can find the correct identifier by temporarily removing the ignore comment and running PHPStan to see the reported error with its identifier.
