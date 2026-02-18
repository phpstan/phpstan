---
title: "logicalAnd.leftAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$zero = 0;
	if ($zero and rand(0, 1)) {
		// unreachable
	}
}
```

## Why is it reported?

The left side of the `and` expression always evaluates to `false`. Since `and` short-circuits, the right side is never evaluated and the entire expression is always `false`. The branch body is dead code. This typically indicates a logic error or a variable that was not assigned the intended value.

## How to fix it

Fix the left-hand condition so it can be true:

```diff-php
 function doFoo(): void
 {
-	$zero = 0;
-	if ($zero and rand(0, 1)) {
+	$value = rand(0, 1);
+	if ($value and rand(0, 1)) {
 		// ...
 	}
 }
```

Or remove the dead branch entirely:

```diff-php
 function doFoo(): void
 {
 	$zero = 0;
-	if ($zero and rand(0, 1)) {
-		// unreachable
-	}
 }
```
