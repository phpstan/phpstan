---
title: "logicalOr.alwaysFalse"
shortDescription: "Result of the or expression is always false."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if ($x !== 0) {
		return;
	}

	// At this point $x is 0
	if (($x > 0) or ($x < 0)) { // ERROR: Result of or is always false.
		echo 'nonzero';
	}
}
```

## Why is it reported?

PHPStan determined that the result of the `or` expression is always `false`. Both comparisons evaluate to `false` for the narrowed type of `$x`, making the entire expression always `false`. This usually indicates dead code, a logic error, or a condition that has already been checked earlier in the control flow.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.alwaysFalse`](/errors/booleanOr.alwaysFalse).

## How to fix it

Remove the redundant condition since the variable has already been narrowed:

```diff-php
 function doFoo(int $x): void
 {
 	if ($x !== 0) {
 		return;
 	}

-	if (($x > 0) or ($x < 0)) {
-		echo 'nonzero';
-	}
+	echo 'zero';
 }
```

Or fix the logic to test what was actually intended:

```diff-php
 function doFoo(int $x): void
 {
-	if ($x !== 0) {
+	if ($x > 10) {
 		return;
 	}

-	if (($x > 0) or ($x < 0)) {
+	if (($x > 5) or ($x < 0)) {
 		echo 'nonzero';
 	}
 }
```
