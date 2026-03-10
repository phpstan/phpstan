---
title: "logicalAnd.rightAlwaysTrue"
shortDescription: "Right side of and is always true, making it redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $flag): bool
{
	$t = true;
	return $flag and $t;
}
```

## Why is it reported?

PHPStan determined that the right side of the `and` expression always evaluates to `true`. This means the right operand does not contribute additional filtering to the condition -- the result depends solely on the left side. This usually indicates redundant logic, a copy-paste mistake, or a condition that has been made unnecessary by earlier narrowing.

The `and` keyword is the low-precedence version of `&&`. This identifier specifically covers the `and` keyword; for `&&`, see [`booleanAnd.rightAlwaysTrue`](/errors/booleanAnd.rightAlwaysTrue).

In the example above, `$t` is always `true`, so the right side of `and` is redundant and the result depends entirely on `$flag`.

## How to fix it

Remove the redundant right side if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $flag): bool
 {
-	$t = true;
-	return $flag and $t;
+	return $flag;
 }
```

Or fix the logic if the condition should test something different:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(bool $flag): bool
+function doFoo(bool $flag, bool $other): bool
 {
-	$t = true;
-	return $flag and $t;
+	return $flag and $other;
 }
```
