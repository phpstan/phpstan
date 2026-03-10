---
title: "greater.alwaysTrue"
shortDescription: "Comparison is always true based on the inferred types."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param positive-int $i */
function doFoo(int $i): void
{
	if ($i > 0) {
		echo 'always positive';
	}
}
```

## Why is it reported?

The `>` comparison always evaluates to `true` based on the types of the operands. In this example, `$i` is a `positive-int` (always `>= 1`), so `$i > 0` is always `true`.

This usually indicates redundant logic or a mistake in the comparison values. The condition adds no meaningful check and can be simplified.

## How to fix it

Remove the redundant condition if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i > 0) {
-		echo 'always positive';
-	}
+	echo 'always positive';
 }
```

Or fix the comparison to check the intended value:

```diff-php
 <?php declare(strict_types = 1);

 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i > 0) {
+	if ($i > 10) {
 		echo 'large positive';
 	}
 }
```
