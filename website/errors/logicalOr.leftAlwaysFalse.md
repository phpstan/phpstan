---
title: "logicalOr.leftAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if ($x > 10) {
		return;
	}

	// At this point $x is <= 10
	$result = ($x > 10) or ($x === 5); // ERROR: Left side of or is always false.
}
```

## Why is it reported?

PHPStan determined that the left side of the `or` expression is always `false`. This means the left operand does not contribute to the result -- the entire expression depends solely on the right side. This usually indicates a logic error, a redundant check, or a condition that has already been narrowed by earlier control flow.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.leftAlwaysFalse`](/errors/booleanOr.leftAlwaysFalse).

## How to fix it

Remove the redundant left side or fix the condition to test what was actually intended:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
 	if ($x > 10) {
 		return;
 	}

-	$result = ($x > 10) or ($x === 5);
+	$result = ($x === 5);
 }
```

Or fix the condition if the logic is wrong:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
 	if ($x > 10) {
 		return;
 	}

-	$result = ($x > 10) or ($x === 5);
+	$result = ($x >= 8) or ($x === 5);
 }
```
