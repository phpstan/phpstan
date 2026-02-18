---
title: "equal.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $s): void
{
	if ($s == new \stdClass()) {
		// never reached
	}
}
```

## Why is it reported?

The loose comparison using `==` always evaluates to `false` based on the types of the operands. Even with PHP's type juggling rules, these two values can never be considered equal. This indicates dead code or a logic error -- the branch will never execute.

## How to fix it

Fix the comparison to compare values that can actually be equal:

```diff-php
 function doFoo(string $s): void
 {
-	if ($s == new \stdClass()) {
+	if ($s == 'expected') {
 		// ...
 	}
 }
```

Or use strict comparison (`===`) if the intent is to compare identical types:

```diff-php
 function doFoo(string $s): void
 {
-	if ($s == new \stdClass()) {
+	if ($s === 'expected') {
 		// ...
 	}
 }
```
