---
title: "logicalOr.rightAlwaysFalse"
shortDescription: "Right side of or is always false, making it redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $flag): bool
{
	$f = false;
	return $flag or $f;
}
```

## Why is it reported?

The right side of the `or` operator always evaluates to `false`. This typically happens because the right-side expression is a value that is always falsy, because the left side already covers all the cases where the right side could be `true`, or because the type of the expression on the right side is narrowed to a point where it can never be `true`. The right operand is redundant and likely indicates a logic error.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.rightAlwaysFalse`](/errors/booleanOr.rightAlwaysFalse).

In the example above, `$f` is always `false`, so the right side of `or` never contributes to the result.

## How to fix it

Remove the redundant right side:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $flag): bool
 {
-	$f = false;
-	return $flag or $f;
+	return $flag;
 }
```

Or fix the right side to test a meaningful condition:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(bool $flag): bool
+function doFoo(bool $flag, bool $other): bool
 {
-	$f = false;
-	return $flag or $f;
+	return $flag or $other;
 }
```
