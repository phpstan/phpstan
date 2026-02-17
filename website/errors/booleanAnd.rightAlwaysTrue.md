---
title: "booleanAnd.rightAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$one = 1;
	if ($i && $one) {
		// ...
	}
}
```

## Why is it reported?

The right side of the `&&` (boolean AND) expression always evaluates to `true`. When the right operand is always truthy, it has no effect on the result of the expression -- the outcome depends entirely on the left operand. This indicates a redundant check, a logic error, or a variable that should hold a different value.

In the example above, `$one` is always `1`, which is truthy in PHP, so the right side of `&&` is always `true` and the condition is equivalent to just `if ($i)`.

## How to fix it

Remove the redundant right operand:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$one = 1;
-	if ($i && $one) {
+	if ($i) {
 		// ...
 	}
 }
```

Or fix the logic to use a variable whose value is not always truthy:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$one = 1;
-	if ($i && $one) {
+	if ($i && $flag) {
 		// ...
 	}
 }
```
