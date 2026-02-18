---
title: "logicalOr.leftAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$one = 1;
	if ($one or $i) {
		// ...
	}
}
```

## Why is it reported?

The left side of the `or` operator is always true. Since `$one` is `1` (a truthy value), the condition will always evaluate to `true` regardless of the value of `$i`. The right side of `or` is never evaluated because PHP uses short-circuit evaluation.

This usually indicates a logic error, leftover debugging code, or a condition that has become redundant after refactoring.

## How to fix it

Fix the condition to test the intended value:

```diff-php
-	if ($one or $i) {
+	if ($i > 0) {
 		// ...
 	}
```

Or remove the always-true left side if only the right side matters:

```diff-php
-	if ($one or $i) {
+	if ($i) {
 		// ...
 	}
```
