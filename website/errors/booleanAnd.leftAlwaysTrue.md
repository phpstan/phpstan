---
title: "booleanAnd.leftAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$one = 1;
	if ($one && $i) {
		// ...
	}
}
```

## Why is it reported?

The left side of the `&&` (boolean AND) expression always evaluates to `true`. When the left operand is always truthy, it has no effect on the result of the expression -- the outcome depends entirely on the right operand. This indicates a redundant check, a logic error, or a variable that should hold a different value.

In the example above, `$one` is always `1`, which is truthy in PHP, so the left side of `&&` is always `true` and the condition is equivalent to just `if ($i)`.

## How to fix it

Remove the redundant left operand:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$one = 1;
-	if ($one && $i) {
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
-	if ($one && $i) {
+	if ($flag && $i) {
 		// ...
 	}
 }
```
