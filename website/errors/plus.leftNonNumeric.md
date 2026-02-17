---
title: "plus.leftNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$null = null;
	$result = $null + 5;
}
```

## Why is it reported?

The `+` operator in PHP is intended for numeric arithmetic (or array union). When a non-numeric value such as `null`, an object, or a non-numeric string is used on the left side of the `+` operator, PHP will attempt implicit type coercion, which is error-prone and usually indicates a bug.

In the example above, `null` is used on the left side of the `+` operator, which PHP silently coerces to `0`.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Ensure the left operand is a numeric type (`int`, `float`, or numeric string):

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $value): void
 {
-	$null = null;
-	$result = $null + 5;
+	$result = $value + 5;
 }
```

Or explicitly convert the value to a number first:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(?int $value): void
 {
-	$result = $value + 5;
+	$result = ($value ?? 0) + 5;
 }
```
