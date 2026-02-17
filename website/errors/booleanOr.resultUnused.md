---
title: "booleanOr.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, bool $b): void
{
	$a || $b;
}
```

## Why is it reported?

The result of the `||` (boolean OR) operator is computed but never used. The expression is evaluated as a standalone statement, meaning its result is discarded. This is usually a mistake -- either the result should be assigned to a variable, returned, or used in a condition.

A common cause of this error is confusing `||` with `or`. The `or` operator has lower precedence, which can lead to unexpected behaviour when mixed with assignment.

## How to fix it

Assign the result to a variable if it is needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$a || $b;
+	$result = $a || $b;
 }
```

Or use the expression as a condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$a || $b;
+	if ($a || $b) {
+		// do something
+	}
 }
```

If the intent was to call a function when `$a` is falsy, consider using an explicit `if` statement instead:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(bool $a, bool $b): void
+function doFoo(bool $a): void
 {
-	$a || $b;
+	if (!$a) {
+		doSomething();
+	}
 }
```
