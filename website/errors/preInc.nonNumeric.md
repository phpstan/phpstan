---
title: "preInc.nonNumeric"
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

function next(string $letter): string
{
	return ++$letter;
}
```

## Why is it reported?

The `++` (pre-increment) operator is being used on a value that is not numeric. While PHP allows incrementing strings (e.g., `'a'` becomes `'b'`), this behaviour can be surprising and error-prone. The strict rules require that only numeric types (`int` or `float`) are used with the increment operator to prevent unintended results.

## How to fix it

Use an explicit operation that makes the intent clear:

```diff-php
 <?php declare(strict_types = 1);

 function next(string $letter): string
 {
-	return ++$letter;
+	return chr(ord($letter) + 1);
 }
```

Or ensure the variable is typed as `int` if you intend numeric increment:

```diff-php
 <?php declare(strict_types = 1);

-function next(string $letter): string
+function next(int $counter): int
 {
-	return ++$letter;
+	return ++$counter;
 }
```
