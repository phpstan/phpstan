---
title: "div.rightNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $numerator, ?string $divisor): void
{
	$result = $numerator / $divisor;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The right-hand side operand of a division (`/`) is not a numeric type. PHP's division operator expects both operands to be numeric (int or float). Using a non-numeric type such as `null`, `array`, `object`, or a non-numeric `string` on the right side of a division will produce unexpected results or a `TypeError` in strict mode. Dividing by `null` is equivalent to dividing by zero, which produces a `DivisionByZeroError`.

## How to fix it

Ensure the right-hand operand is a numeric type by adding a type check or providing a default value:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $numerator, ?string $divisor): void
+function doFoo(int $numerator, float $divisor): void
 {
 	$result = $numerator / $divisor;
 }
```

Or validate and convert the value before use:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $numerator, ?string $divisor): void
 {
-	$result = $numerator / $divisor;
+	if ($divisor !== null && is_numeric($divisor)) {
+		$result = $numerator / (float) $divisor;
+	}
 }
```
