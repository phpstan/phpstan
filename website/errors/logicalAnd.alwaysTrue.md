---
title: "logicalAnd.alwaysTrue"
shortDescription: "Result of the and expression is always true."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param positive-int $i */
function doFoo(int $i): bool
{
	return ($i > 0) and (is_int($i));
}
```

## Why is it reported?

The result of the `and` expression is always `true`. Both sides of the logical AND are always true given the types and conditions involved, making the check redundant. This often indicates duplicated conditions or overly broad type constraints that make the test meaningless.

The `and` keyword is the low-precedence version of `&&`. This identifier specifically covers the `and` keyword; for `&&`, see [`booleanAnd.alwaysTrue`](/errors/booleanAnd.alwaysTrue).

In the example above, `$i` is a `positive-int`, so `$i > 0` is always `true`, and `is_int($i)` is also always `true` because `$i` is typed as `int`.

## How to fix it

Remove the redundant expression:

```diff-php
 /** @param positive-int $i */
-function doFoo(int $i): bool
+function doFoo(int $i): true
 {
-	return ($i > 0) and (is_int($i));
+	return true;
 }
```

Or replace the conditions with meaningful checks:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): bool
 {
-	return ($i > 0) and (is_int($i));
+	return ($i > 0) and ($i < 100);
 }
```
