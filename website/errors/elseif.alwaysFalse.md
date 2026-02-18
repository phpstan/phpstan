---
title: "elseif.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i === 1) {
		// ...
	} elseif ($i === 1) {
		// unreachable
	}
}
```

## Why is it reported?

The `elseif` condition always evaluates to `false`, which means the branch can never be entered. This typically happens when the condition was already covered by a previous `if` or `elseif` branch, or when the types involved make the condition logically impossible. Code inside this branch is dead code and likely indicates a logic error.

## How to fix it

Fix the condition so it tests something that can actually be true:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i === 1) {
 		// ...
-	} elseif ($i === 1) {
+	} elseif ($i === 2) {
 		// ...
 	}
 }
```

Or remove the unreachable branch entirely:

```diff-php
 function doFoo(int $i): void
 {
 	if ($i === 1) {
 		// ...
-	} elseif ($i === 1) {
-		// unreachable
 	}
 }
```
