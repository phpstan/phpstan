---
title: "smaller.alwaysTrue"
shortDescription: "Less-than comparison always evaluates to true."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param negative-int $i */
function doFoo(int $i): void
{
	if ($i < 0) {
		echo 'always negative';
	}
}
```

## Why is it reported?

The `<` comparison is always true based on the types of the operands. This indicates that the condition is redundant because the left side is always strictly less than the right side given their possible values. Such comparisons often signal a logic error or an overly broad type.

In the example above, `$i` is a `negative-int` (always `<= -1`), so `$i < 0` is always `true`.

## How to fix it

Remove the unnecessary condition:

```diff-php
 /** @param negative-int $i */
 function doFoo(int $i): void
 {
-	if ($i < 0) {
-		echo 'always negative';
-	}
+	echo 'always negative';
 }
```

Or adjust the comparison to reflect the actual constraint you intend to enforce:

```diff-php
 /** @param negative-int $i */
 function doFoo(int $i): void
 {
-	if ($i < 0) {
+	if ($i < -10) {
 		echo 'very negative';
 	}
 }
```
