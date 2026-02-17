---
title: "logicalAnd.rightAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if ($x > 0 and $x >= 0) { // ERROR: Right side of and is always true.
		echo 'positive';
	}
}
```

## Why is it reported?

PHPStan determined that the right side of the `and` expression always evaluates to `true`. This means the right operand does not contribute additional filtering to the condition -- the result depends solely on the left side. This usually indicates redundant logic, a copy-paste mistake, or a condition that has been made unnecessary by earlier narrowing.

The `and` keyword is the low-precedence version of `&&`. This identifier specifically covers the `and` keyword; for `&&`, see [`booleanAnd.rightAlwaysTrue`](/errors/booleanAnd.rightAlwaysTrue).

In the example above, when `$x > 0` is true, `$x >= 0` is necessarily also true, making the right side redundant.

## How to fix it

Remove the redundant right side if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x > 0 and $x >= 0) {
+	if ($x > 0) {
 		echo 'positive';
 	}
 }
```

Or fix the logic if the condition should test something different:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x > 0 and $x >= 0) {
+	if ($x > 0 and $x < 100) {
 		echo 'positive';
 	}
 }
```
