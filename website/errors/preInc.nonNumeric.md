---
title: "preInc.nonNumeric"
shortDescription: "Pre-increment operator is used on a non-numeric value."
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

function increment(bool $flag): int
{
	return ++$flag;
}
```

## Why is it reported?

The `++` (pre-increment) operator is being used on a value that is not numeric. While PHP allows incrementing non-numeric types like `bool`, this behaviour can be surprising and error-prone. The strict rules require that only numeric types (`int` or `float`) are used with the increment operator to prevent unintended results.

## How to fix it

Ensure the variable is typed as `int` if you intend numeric increment:

```diff-php
 <?php declare(strict_types = 1);

-function increment(bool $flag): int
+function increment(int $counter): int
 {
-	return ++$flag;
+	return ++$counter;
 }
```
