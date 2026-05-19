---
title: "goto.labelUndefined"
shortDescription: "A goto statement targets a label that is not defined in the same scope."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	goto nonexistent;
}
```

## Why is it reported?

The `goto` statement targets a label that does not exist in the same function or method scope. PHP requires the target label to be defined within the same scope as the `goto` statement. Jumping to a label defined outside the current function, closure, or class method is not allowed.

Common cases where this error occurs:

- The label name is misspelled or does not exist at all
- The label is defined in an enclosing function, but the `goto` is inside a closure or anonymous class
- The label is defined inside a closure or nested function, but the `goto` is in the outer function

This is a fatal error in PHP and cannot be ignored.

## How to fix it

Define the target label in the same scope as the `goto` statement:

```diff-php
 function doFoo(): void
 {
-	goto nonexistent;
+	goto end;
+	echo 'skipped';
+	end:
+	echo 'done';
 }
```

If the `goto` is trying to jump across a closure or anonymous class boundary, restructure the code to avoid `goto` entirely:

```diff-php
 function doFoo(): void
 {
-	outside:
 	$fn = function () {
-		goto outside;
+		return;
 	};
 }
```
