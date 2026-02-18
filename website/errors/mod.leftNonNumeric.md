---
title: "mod.leftNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function remainder(string $a, int $b): int
{
	return $a % $b;
}
```

## Why is it reported?

The left operand of the modulo operator (`%`) is not a numeric type. PHP will attempt to coerce non-numeric values to numbers at runtime, which can lead to unexpected results or `TypeError` exceptions in strict mode. Only `int` and `float` types should be used in arithmetic operations.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Ensure the left operand is a numeric type before performing the modulo operation:

```diff-php
-function remainder(string $a, int $b): int
+function remainder(int $a, int $b): int
 {
 	return $a % $b;
 }
```

If the value is a numeric string, cast it explicitly:

```diff-php
 function remainder(string $a, int $b): int
 {
-	return $a % $b;
+	return (int) $a % $b;
 }
```
