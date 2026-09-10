---
title: "return.byRefFinally"
shortDescription: "A finally block modifies the value a function returns by reference, so the caller receives a type that no longer matches the declared return type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function &byRefChangedInFinally(): int
{
	$x = 0;
	try {
		return $x;
	} finally {
		$x = 'test';
	}
}
```

## Why is it reported?

A function that returns by reference (declared with `&`) hands the caller a reference to the returned variable rather than a copy of its value. A `finally` block always runs *after* the `return` statement is evaluated but *before* control leaves the function, so any change it makes to the returned variable is what the caller actually receives.

In the example, `return $x;` captures `$x` while it still holds the `int` `0`, but the `finally` block then assigns the `string` `'test'` to it. Because the reference is still bound to `$x`, the caller ends up with `'test'`, which violates the declared `int` return type.

This differs from a function that returns by value: without `&`, the returned value is copied at the `return` statement, so a later `finally` assignment cannot affect it. The rule only reports functions that return by reference and are not generators.

## How to fix it

Do not modify the returned variable inside the `finally` block:

```diff-php
 function &byRefChangedInFinally(): int
 {
 	$x = 0;
 	try {
 		return $x;
 	} finally {
-		$x = 'test';
 	}
 }
```

If the `finally` block genuinely needs to reset or clean up its own state, use a separate variable so the returned reference is left untouched:

```diff-php
 function &byRefChangedInFinally(): int
 {
 	$x = 0;
+	$cleanup = 0;
 	try {
 		return $x;
 	} finally {
-		$x = 'test';
+		$cleanup = 'test';
 	}
 }
```

If the function does not actually need to return by reference, drop the `&` so the value is copied at the `return` statement and the `finally` block can no longer affect the result:

```diff-php
-function &byRefChangedInFinally(): int
+function byRefChangedInFinally(): int
 {
 	$x = 0;
 	try {
 		return $x;
 	} finally {
 		$x = 'test';
 	}
 }
```
