---
title: "elseif.alwaysFalse"
shortDescription: "Elseif condition is always false, making the branch unreachable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$flag = 0;
	if ($i > 10) {
		echo 'big';
	} elseif ($flag) {
		echo 'unreachable';
	}
}
```

## Why is it reported?

The `elseif` condition always evaluates to `false`, which means the branch can never be entered. This typically happens when the condition is a value that is always falsy, when it was already covered by a previous `if` or `elseif` branch, or when the types involved make the condition logically impossible. Code inside this branch is dead code and likely indicates a logic error.

In the example above, `$flag` is always `0` (falsy), so the `elseif` branch can never be entered.

## How to fix it

Fix the condition so it tests something that can actually be true:

```diff-php
 function doFoo(int $i): void
 {
+	$flag = rand(0, 1);
 	if ($i > 10) {
 		echo 'big';
-	} elseif ($flag) {
+	} elseif ($flag === 1) {
 		// ...
 	}
 }
```

Or remove the unreachable branch entirely:

```diff-php
 function doFoo(int $i): void
 {
-	$flag = 0;
 	if ($i > 10) {
 		echo 'big';
-	} elseif ($flag) {
-		echo 'unreachable';
 	}
 }
```
