---
title: "greater.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i > 5) {
		if ($i > 2) {
			// always true
		}
	}
}

function doBar(int $j): void
{
	if ($j >= 2 && $j < 5) {
		if ($j > 8) {
			// always false
		}
	}
}
```

## Why is it reported?

The comparison using the `>` operator is always false based on the types PHPStan has inferred. In the second example, inside the `if ($j >= 2 && $j < 5)` block, the value of `$j` is in the range `[2, 4]`, so `$j > 8` can never be true. The code inside the condition is dead code and will never execute.

This usually indicates a logic error where the condition does not match the developer's intent, or the code structure has been refactored and the condition is now redundant.

## How to fix it

Fix the comparison to match the intended logic:

```diff-php
 if ($j >= 2 && $j < 5) {
-	if ($j > 8) {
+	if ($j > 3) {
 		// ...
 	}
 }
```

Or remove the condition entirely if the dead code is not needed:

```diff-php
 if ($j >= 2 && $j < 5) {
-	if ($j > 8) {
-		// ...
-	}
+	// ...
 }
```
