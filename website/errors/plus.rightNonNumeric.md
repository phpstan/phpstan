---
title: "plus.rightNonNumeric"
shortDescription: "Right side of the + operator is a non-numeric value."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function add(int $a, bool $flag): int
{
	return $a + $flag;
}
```

## Why is it reported?

The `+` operator in PHP performs arithmetic addition on numeric types or array union on arrays. Using a non-numeric, non-array type on the right side of `+` is a type error that leads to unexpected behaviour or a runtime fatal error.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

## How to fix it

Ensure the right operand is a numeric type:

```diff-php
-function add(int $a, bool $flag): int
+function add(int $a, int $b): int
 {
-	return $a + $flag;
+	return $a + $b;
 }
```

Or cast the value explicitly:

```diff-php
 function add(int $a, bool $flag): int
 {
-	return $a + $flag;
+	return $a + (int) $flag;
 }
```
