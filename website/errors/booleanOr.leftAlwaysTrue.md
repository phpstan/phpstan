---
title: "booleanOr.leftAlwaysTrue"
shortDescription: "Left side of || always evaluates to true, so the right side is never evaluated."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$t = true;
	if ($t || $i > 0) {
		echo 'left always true';
	}
}
```

## Why is it reported?

The left side of a `||` expression always evaluates to `true`. Because `||` uses short-circuit evaluation, when the left side is always true, the right side is never evaluated, making the entire expression always `true`.

In the example above, `$t` is always `true`, so `$i > 0` on the right side is never evaluated.

## How to fix it

Simplify the condition by removing the redundant parts:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$t = true;
-	if ($t || $i > 0) {
-		echo 'left always true';
-	}
+	echo 'left always true';
 }
```

Or fix the left side if it should not always be true:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$t = true;
-	if ($t || $i > 0) {
+	if ($flag || $i > 0) {
 		echo 'something';
 	}
 }
```
