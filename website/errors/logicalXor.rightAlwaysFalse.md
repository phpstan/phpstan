---
title: "logicalXor.rightAlwaysFalse"
shortDescription: "Right side of xor is always false, making the operator redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $flag): bool
{
	$f = false;
	return $flag xor $f;
}
```

## Why is it reported?

The right side of the `xor` operator always evaluates to `false`. This makes the `xor` expression equivalent to just the left side, because `$left xor false` always equals `$left`. The right operand is redundant and likely indicates a logic error.

In the example above, `$f` is always `false`, so `$flag xor $f` is equivalent to `$flag`.

## How to fix it

Simplify the expression by removing the redundant `xor`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $flag): bool
 {
-	$f = false;
-	return $flag xor $f;
+	return $flag;
 }
```

Or fix the right-side condition so that it can actually evaluate to `true`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(bool $flag): bool
+function doFoo(bool $flag, bool $other): bool
 {
-	$f = false;
-	return $flag xor $f;
+	return $flag xor $other;
 }
```
