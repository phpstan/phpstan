---
title: "logicalOr.rightAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i, stdClass $std): void
{
	if ($i or $std) {
		// ...
	}
}
```

## Why is it reported?

The right side of the `or` operator always evaluates to `true`. In the example, `$std` is of type `stdClass`, which is an object and is always truthy in a boolean context. This means the right side of the `or` expression can never be `false`, making the condition partially redundant.

## How to fix it

If the condition is truly meant to always be true at that point, simplify it:

```diff-php
 function doFoo(int $i, stdClass $std): void
 {
-	if ($i or $std) {
-		// ...
-	}
+	// ...
 }
```

Or fix the logic to use the intended variable or comparison:

```diff-php
-function doFoo(int $i, stdClass $std): void
+function doFoo(int $i, ?stdClass $std): void
 {
 	if ($i or $std) {
 		// ...
 	}
 }
```
