---
title: "booleanAnd.alwaysTrue"
shortDescription: "Result of && is always true."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param positive-int $i */
function doFoo(int $i): void
{
	if ($i > 0 && is_int($i)) {
		echo 'always';
	}
}
```

## Why is it reported?

The result of the `&&` (boolean AND) expression always evaluates to `true`. This happens when both sides of the operator are always truthy, making the condition constant. In this example, `$i` is a `positive-int`, so `$i > 0` is always `true` and `is_int($i)` is also always `true`. This usually indicates a logic error, a redundant check, or dead code.

## How to fix it

Remove the redundant condition if the check is unnecessary:

```diff-php
 <?php declare(strict_types = 1);

 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i > 0 && is_int($i)) {
-		echo 'always';
-	}
+	echo 'always';
 }
```

Or fix the logic to use conditions that are meaningful:

```diff-php
 <?php declare(strict_types = 1);

 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i > 0 && is_int($i)) {
+	if ($i > 0 && $i < 100) {
 		echo 'always';
 	}
 }
```
