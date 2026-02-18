---
title: "booleanAnd.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, bool $b): void
{
	$a && $b;
}
```

## Why is it reported?

The result of the `&&` (boolean AND) operator is computed but never used. The expression is evaluated as a standalone statement, meaning its result is discarded. This is usually a mistake -- either the result should be assigned to a variable, returned, or used in a condition.

## How to fix it

Assign the result to a variable if it is needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$a && $b;
+	$result = $a && $b;
 }
```

Or use the expression as a condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$a && $b;
+	if ($a && $b) {
+		// do something
+	}
 }
```

If the intent was to call a function when `$a` is truthy, consider using an explicit `if` statement instead:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(bool $a, bool $b): void
+function doFoo(bool $a): void
 {
-	$a && $b;
+	if ($a) {
+		doSomething();
+	}
 }
```
