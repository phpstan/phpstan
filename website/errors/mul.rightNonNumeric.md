---
title: "mul.rightNonNumeric"
shortDescription: "Right side of the multiplication operator is not a numeric type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function multiply(int $a, bool $flag): int
{
	return $a * $flag;
}
```

## Why is it reported?

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The right operand of the multiplication operator (`*`) is not a numeric type. PHP will attempt to cast non-numeric values to numbers, which can lead to unexpected results or warnings. Strict rules require that only numeric types are used in arithmetic operations.

## How to fix it

Ensure the operand is a numeric type:

```diff-php
-function multiply(int $a, bool $flag): int
+function multiply(int $a, int $b): int
 {
-	return $a * $flag;
+	return $a * $b;
 }
```

Or explicitly cast the value:

```diff-php
 function multiply(int $a, bool $flag): int
 {
-	return $a * $flag;
+	return $a * (int) $flag;
 }
```
