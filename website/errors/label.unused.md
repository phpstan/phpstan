---
title: "label.unused"
shortDescription: "A label is defined but no goto statement references it."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	unused:
	echo 'hello';
}
```

## Why is it reported?

The label `unused` is defined but there is no `goto` statement that references it. Labels only have meaning as targets for `goto` statements. An unreferenced label is dead code that likely indicates leftover code from refactoring or a missing `goto` statement.

A label is also considered unused when its corresponding `goto` statement is in a different scope. Labels defined in an outer function are not reachable from closures, anonymous classes, or nested functions.

## How to fix it

Remove the unused label:

```diff-php
 function doFoo(): void
 {
-	unused:
 	echo 'hello';
 }
```

Or add the corresponding `goto` statement if the label was intended to be used:

```diff-php
 function doFoo(): void
 {
+	if (rand(0, 1) === 0) {
+		goto end;
+	}
+	echo 'hello';
-	unused:
-	echo 'hello';
+	end:
+	echo 'done';
 }
```
