---
title: "mul.leftNonNumeric"
shortDescription: "Left side of the multiplication operator is not a numeric type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function multiply(bool $flag, int $b): int
{
	return $flag * $b;
}
```

## Why is it reported?

The left operand of the multiplication operator (`*`) is not a numeric type. PHP will attempt to coerce non-numeric values to numbers at runtime, which can lead to unexpected results or `TypeError` exceptions in strict mode. Only `int` and `float` types should be used in arithmetic operations.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Ensure the left operand is a numeric type before performing multiplication:

```diff-php
-function multiply(bool $flag, int $b): int
+function multiply(int $a, int $b): int
 {
-	return $flag * $b;
+	return $a * $b;
 }
```

Or cast the value explicitly:

```diff-php
 function multiply(bool $flag, int $b): int
 {
-	return $flag * $b;
+	return (int) $flag * $b;
 }
```
