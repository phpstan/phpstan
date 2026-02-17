---
title: "booleanOr.rightAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $x): void
{
	if ($x < 0 || $x >= 0) {
		// ...
	}
}
```

## Why is it reported?

The right side of a `||` (or `or`) expression always evaluates to `true`. This means the entire expression is always true regardless of what the left side evaluates to, which usually indicates a logic error or a redundant condition.

In the example above, when `$x < 0` is false, then `$x >= 0` must be true, so the right side is always true when it is reached.

## How to fix it

Review the logic of the condition. The right side being always true usually means the condition is redundant and can be simplified:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x < 0 || $x >= 0) {
+	if (true) {
 		// ...
 	}
 }
```

Or the condition may contain a logic error and one of the sides needs to be adjusted:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $x): void
 {
-	if ($x < 0 || $x >= 0) {
+	if ($x < 0 || $x > 10) {
 		// ...
 	}
 }
```
