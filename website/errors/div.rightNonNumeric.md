---
title: "div.rightNonNumeric"
shortDescription: "Right side of the division operator is not a numeric type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $numerator, bool $flag): void
{
	$result = $numerator / $flag;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The right-hand side operand of a division (`/`) is not a numeric type. PHP's division operator expects both operands to be numeric (int or float). Using a non-numeric type such as `bool`, `null`, `array`, or `object` on the right side of a division will produce unexpected results or a `TypeError` in strict mode.

## How to fix it

Ensure the right-hand operand is a numeric type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $numerator, bool $flag): void
+function doFoo(int $numerator, float $divisor): void
 {
-	$result = $numerator / $flag;
+	$result = $numerator / $divisor;
 }
```

Or convert the value to a numeric type before use:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $numerator, bool $flag): void
 {
-	$result = $numerator / $flag;
+	$result = $numerator / (int) $flag;
 }
```
