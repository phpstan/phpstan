---
title: "greaterOrEqual.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i >= 2 && $i < 5) {
		if ($i >= 1) {
			// ...
		}
	}
}
```

## Why is it reported?

The `>=` comparison always evaluates to `true` based on the types of the operands. In the example, `$i` is already known to be at least `2` from the outer condition, so `$i >= 1` is always `true`. This usually indicates a logic error, redundant condition, or incorrect comparison value.

## How to fix it

Fix the comparison to reflect the intended logic:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i >= 2 && $i < 5) {
-		if ($i >= 1) {
+		if ($i >= 3) {
 			// ...
 		}
 	}
 }
```

Or remove the redundant condition:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i >= 2 && $i < 5) {
-		if ($i >= 1) {
-			// ...
-		}
+		// ...
 	}
 }
```
