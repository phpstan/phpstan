---
title: "smallerOrEqual.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i < 2) {
		if ($i <= 5) {
			// ...
		}
	}
}
```

## Why is it reported?

The `<=` comparison is always true based on the types PHPStan has inferred for the operands. In the example above, after `$i < 2` narrows `$i` to `int<min, 1>`, the comparison `$i <= 5` is always true because any value less than 2 is also less than or equal to 5.

A comparison that is always true usually indicates a logic error, a redundant condition, or an overly constrained type.

## How to fix it

Fix the comparison to express the intended condition:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i < 2) {
-		if ($i <= 5) {
+		if ($i <= 0) {
 			// ...
 		}
 	}
 }
```

Or remove the redundant condition:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i < 2) {
-		if ($i <= 5) {
-			// ...
-		}
+		// ...
 	}
 }
```

If the condition is always true because of a PHPDoc type, and you believe the condition is still meaningful at runtime, the tip on the error will suggest setting [`treatPhpDocTypesAsCertain`](/config-reference#treatphpdoctypesascertain) to `false`.
