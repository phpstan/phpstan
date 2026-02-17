---
title: "greater.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i < 2) {
		if ($i < 5) {
			// always entered when $i < 2
		}
	}
}
```

## Why is it reported?

The comparison operation using `>`, `>=`, `<`, or `<=` always evaluates to `true` based on the types of the operands. In this example, the inner condition `$i < 5` is always `true` because the outer condition already guarantees that `$i < 2`, and any value less than 2 is also less than 5.

This usually indicates redundant logic or a mistake in the comparison values. The condition adds no meaningful check and can be simplified.

## How to fix it

Remove the redundant condition if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
 	if ($i < 2) {
-		if ($i < 5) {
-			// always entered when $i < 2
-		}
+		// execute directly, no need for the redundant check
 	}
 }
```

Or fix the comparison to check the intended value:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
 	if ($i < 2) {
-		if ($i < 5) {
+		if ($i < 0) {
 			// now this condition is meaningful
 		}
 	}
 }
```
