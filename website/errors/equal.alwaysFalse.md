---
title: "equal.alwaysFalse"
shortDescription: "Loose comparison using == will always evaluate to false."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param positive-int $i */
function doFoo(int $i): void
{
	if ($i == 0) {
		// never reached
	}
}
```

## Why is it reported?

The loose comparison using `==` always evaluates to `false` based on the types of the operands. Even with PHP's type juggling rules, these two values can never be considered equal. This indicates dead code or a logic error -- the branch will never execute.

In the example above, `$i` is a `positive-int` (always `>= 1`), so it can never be loosely equal to `0`.

## How to fix it

Fix the comparison to compare values that can actually be equal:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i == 0) {
+	if ($i == 1) {
 		// ...
 	}
 }
```

Or use strict comparison (`===`) if the intent is to compare identical types:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i == 0) {
+	if ($i === 1) {
 		// ...
 	}
 }
```
