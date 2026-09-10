---
title: "return.byRefFinally"
shortDescription: "A finally block changes the value a by-reference return hands back to the caller, breaking the declared return type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function &doFoo(): int
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

A function that returns by reference (`function &doFoo()`) hands the caller a reference to the returned variable instead of a copy of its value. The `finally` block runs after the `return` expression is evaluated but before control is handed back to the caller, so any change it makes to that variable is what the caller actually receives.

Here the `return` statement points at `int` `$x`, but the `finally` block reassigns `$x` to a `string`. Because the return is by reference, the caller ends up with the `string`, violating the declared `int` return type.

## How to fix it

Do not reassign the returned variable inside the `finally` block:

```diff-php
 function &doFoo(): int
 {
 	$x = 0;
 	try {
 		return $x;
 	} finally {
-		$x = 'test';
 	}
 }
```

If the reference semantics are not needed, return by value by removing the `&`. The `finally` block then no longer affects the returned value:

```diff-php
-function &doFoo(): int
+function doFoo(): int
 {
 	$x = 0;
 	try {
 		return $x;
 	} finally {
 		$x = 'test';
 	}
 }
```
