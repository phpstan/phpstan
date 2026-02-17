---
title: "preDec.nonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $s): void
{
	--$s;
}
```

## Why is it reported?

The pre-decrement operator `--` is intended for numeric types. Applying it to a non-numeric value such as `string`, `bool`, `null`, or an object does not produce meaningful results and is likely a bug.

This error is reported by `phpstan/phpstan-strict-rules`.

## How to fix it

Convert the value to an appropriate numeric type before decrementing, or use explicit arithmetic:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(string $s): void
+function doFoo(int $count): void
 {
-	--$s;
+	--$count;
 }
```
