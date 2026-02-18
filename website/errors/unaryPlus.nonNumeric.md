---
title: "unaryPlus.nonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(?int $value): void
{
	$result = +$value;
}
```

## Why is it reported?

This error is reported by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

The unary `+` operator is applied to a non-numeric value. The unary plus operator is intended for numeric types (`int`, `float`). When applied to non-numeric types like `null`, `string`, or objects, PHP silently coerces the value, which can lead to unexpected results and hides type errors.

## How to fix it

Ensure the operand is a numeric type before applying unary `+`:

```diff-php
 function doFoo(?int $value): void
 {
-	$result = +$value;
+	if ($value !== null) {
+		$result = +$value;
+	}
 }
```

Or cast the value explicitly if the coercion is intentional:

```diff-php
 function doFoo(?int $value): void
 {
-	$result = +$value;
+	$result = (int) $value;
 }
```
