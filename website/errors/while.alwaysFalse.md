---
title: "while.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	while (false) {
		echo 'unreachable';
	}
}
```

## Why is it reported?

The condition of the `while` loop always evaluates to `false`, which means the loop body will never execute. This is dead code and usually indicates a logic error, such as a condition that was incorrectly written or a variable whose type has been narrowed to a state that makes the condition impossible.

## How to fix it

Fix the condition so that the loop can actually execute:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(bool $condition): void
 {
-	while (false) {
+	while ($condition) {
 		echo 'reachable';
+		$condition = false;
 	}
 }
```

Or remove the dead code if the loop is no longer needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	while (false) {
-		echo 'unreachable';
-	}
 }
```
