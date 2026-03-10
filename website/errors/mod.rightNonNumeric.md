---
title: "mod.rightNonNumeric"
shortDescription: "Right side of the modulo operator is not a numeric type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, bool $flag): void
{
	$result = $a % $flag;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The right-hand side operand of a modulo (`%`) is not a numeric type. PHP's modulo operator expects both operands to be numeric (int or float). Using a non-numeric type such as `bool`, `null`, `array`, or `object` on the right side of a modulo operation will produce unexpected results or a `TypeError` in strict mode.

## How to fix it

Ensure the right-hand operand is a numeric type by narrowing the type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $a, bool $flag): void
+function doFoo(int $a, int $b): void
 {
-	$result = $a % $flag;
+	$result = $a % $b;
 }
```

Or cast the value before use:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $a, bool $flag): void
 {
-	$result = $a % $flag;
+	$result = $a % (int) $flag;
 }
```
