---
title: "div.leftNonNumeric"
shortDescription: "Left side of the division operator is not a numeric type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function divide(bool $flag, int $denominator): float
{
	return $flag / $denominator;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The left operand of a division operator (`/`) is not a numeric type (int or float). Division is an arithmetic operation that only makes sense with numeric values. Using a non-numeric type like `bool`, `array`, `object`, or `null` on the left side of a division indicates a logic error.

In the example above, `$flag` is a `bool`, which is not a valid numeric operand for division.

## How to fix it

Use a numeric value as the left operand:

```diff-php
 <?php declare(strict_types = 1);

-function divide(bool $flag, int $denominator): float
+function divide(int $numerator, int $denominator): float
 {
-	return $flag / $denominator;
+	return $numerator / $denominator;
 }
```

Or convert the value to a numeric type before dividing:

```diff-php
 <?php declare(strict_types = 1);

 function divide(bool $flag, int $denominator): float
 {
-	return $flag / $denominator;
+	return (int) $flag / $denominator;
 }
```
