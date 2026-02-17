---
title: "div.leftNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function divide(string $label, int $denominator): void
{
	$result = $label / $denominator;
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The left operand of a division operator (`/`) is not a numeric type (int or float). Division is an arithmetic operation that only makes sense with numeric values. Using a non-numeric type like `string`, `array`, `object`, or `null` on the left side of a division indicates a logic error.

In the example above, `$label` is a `string`, which is not a valid numeric operand for division.

## How to fix it

Use a numeric value as the left operand:

```diff-php
 <?php declare(strict_types = 1);

-function divide(string $label, int $denominator): void
+function divide(int $numerator, int $denominator): void
 {
-	$result = $label / $denominator;
+	$result = $numerator / $denominator;
 }
```

Or convert the value to a numeric type before dividing:

```diff-php
 <?php declare(strict_types = 1);

 function divide(string $label, int $denominator): void
 {
-	$result = $label / $denominator;
+	$result = (int) $label / $denominator;
 }
```
