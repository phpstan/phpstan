---
title: "pow.leftNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$null = null;
	$result = $null ** 2;
}
```

## Why is it reported?

The exponentiation operator (`**`) is intended for numeric arithmetic. When a non-numeric value such as `null`, an object, or a non-numeric string is used on the left side of the `**` operator, PHP will attempt implicit type coercion, which is error-prone and usually indicates a bug.

In the example above, `null` is used as the base of the exponentiation, which PHP silently coerces to `0`.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Ensure the left operand is a numeric type (`int`, `float`, or numeric string):

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $base): void
 {
-	$null = null;
-	$result = $null ** 2;
+	$result = $base ** 2;
 }
```

Or handle the possibly-null value explicitly:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(?int $base): void
 {
-	$result = $base ** 2;
+	$result = ($base ?? 0) ** 2;
 }
```
