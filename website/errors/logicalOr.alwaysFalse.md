---
title: "logicalOr.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if (($x > 10) or ($x < 0) or ($x === 5)) {
		return;
	}

	// At this point $x is >= 0 and <= 10 and not 5
	$result = ($x > 10) or ($x < 0); // ERROR: Result of or is always false.
}
```

## Why is it reported?

PHPStan determined that the result of the `or` expression is always `false`. Both the left side and the right side are always `false`, making the entire expression always `false`. This usually indicates dead code, a logic error, or a condition that has already been checked earlier in the control flow.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.alwaysFalse`](/errors/booleanOr.alwaysFalse).

## How to fix it

Review the logic and remove the redundant condition or fix the expression to test what was actually intended:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
 	if (($x > 10) or ($x < 0) or ($x === 5)) {
 		return;
 	}

-	$result = ($x > 10) or ($x < 0);
+	$result = ($x >= 5) or ($x <= 2);
 }
```
