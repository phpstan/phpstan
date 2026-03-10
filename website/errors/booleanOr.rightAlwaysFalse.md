---
title: "booleanOr.rightAlwaysFalse"
shortDescription: "Right side of || always evaluates to false, making it redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$f = false;
	if ($i > 0 || $f) {
		echo 'right always false';
	}
}
```

## Why is it reported?

The right side of the `||` operator always evaluates to `false`. This means the right operand never contributes to the condition -- the result depends solely on the left side. This usually indicates redundant logic, a copy-paste mistake, or a condition that has been made unnecessary.

In the example above, `$f` is always `false`, so the right side of `||` is redundant and the entire condition is equivalent to just `$i > 0`.

## How to fix it

Remove the redundant right-side condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$f = false;
-	if ($i > 0 || $f) {
+	if ($i > 0) {
 		echo 'right always false';
 	}
 }
```

Or fix the right side to use a meaningful condition:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$f = false;
-	if ($i > 0 || $f) {
+	if ($i > 0 || $flag) {
 		// ...
 	}
 }
```
