---
title: "mod.rightNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, ?string $b): void
{
	$result = $a % $b;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The right-hand side operand of a modulo (`%`) is not a numeric type. PHP's modulo operator expects both operands to be numeric (int, float, or numeric-string). Using a non-numeric type such as `null`, `array`, `object`, or a non-numeric `string` on the right side of a modulo operation will produce unexpected results or a `TypeError` in strict mode. A `null` right operand is equivalent to zero, which causes a `DivisionByZeroError`.

## How to fix it

Ensure the right-hand operand is a numeric type by narrowing the type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $a, ?string $b): void
+function doFoo(int $a, int $b): void
 {
 	$result = $a % $b;
 }
```

Or validate and convert the value before use:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $a, ?string $b): void
 {
-	$result = $a % $b;
+	if ($b !== null && is_numeric($b)) {
+		$result = $a % (int) $b;
+	}
 }
```
