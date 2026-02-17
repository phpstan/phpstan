---
title: "booleanOr.leftAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if ($x >= 0 || $x > 5) {
		// ...
	}
}
```

## Why is it reported?

The left side of a `||` (or `or`) expression always evaluates to `true`. Because `||` uses short-circuit evaluation, when the left side is always true, the right side is never evaluated, making the entire expression pointless.

In the example above, if `$x >= 0` is always true in the given scope, the condition `$x > 5` on the right side is unreachable.

## How to fix it

Review the logic of the condition. The left side being always true usually means one of the following:

The condition is redundant and can be simplified:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x >= 0 || $x > 5) {
+	if ($x >= 0) {
 		// ...
 	}
 }
```

Or the condition contains a logic error and should use `&&` instead:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x >= 0 || $x > 5) {
+	if ($x >= 0 && $x > 5) {
 		// ...
 	}
 }
```
