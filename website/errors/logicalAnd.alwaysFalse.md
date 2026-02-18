---
title: "logicalAnd.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i > 5 and $i < 3) {
		// unreachable
	}
}
```

## Why is it reported?

The result of the `and` expression is always `false`. Given the types and conditions involved, there is no possible value that satisfies both sides of the logical AND simultaneously. The code inside the branch is dead code and likely indicates a logic error.

## How to fix it

Fix the conditions so they can both be true at the same time:

```diff-php
 function doFoo(int $i): void
 {
-	if ($i > 5 and $i < 3) {
+	if ($i > 3 and $i < 5) {
 		// ...
 	}
 }
```

Or, if the intent was an OR condition:

```diff-php
 function doFoo(int $i): void
 {
-	if ($i > 5 and $i < 3) {
+	if ($i > 5 or $i < 3) {
 		// ...
 	}
 }
```
