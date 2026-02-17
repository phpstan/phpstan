---
title: "unaryMinus.nonNumeric"
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(?int $value): void
{
	$result = -$value;
}
```

## Why is it reported?

The unary minus operator (`-`) is applied to a non-numeric value. In PHP, applying unary minus to non-numeric types like `null`, `bool`, `string`, `array`, or `object` produces unexpected results or errors. This rule enforces that only numeric types (`int`, `float`, or their union types) are used with the unary minus operator.

In the example above, `$value` has type `int|null`, and applying `-` to `null` is not a valid numeric operation.

## How to fix it

Ensure the operand is a numeric type before applying unary minus:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(?int $value): void
 {
+	if ($value === null) {
+		return;
+	}
+
 	$result = -$value;
 }
```

Or change the parameter type to exclude non-numeric types:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(?int $value): void
+function doFoo(int $value): void
 {
 	$result = -$value;
 }
```
