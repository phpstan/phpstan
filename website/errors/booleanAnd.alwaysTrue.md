---
title: "booleanAnd.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$one = 1;
	$result = $one && $i;
}
```

## Why is it reported?

The result of the `&&` (boolean AND) expression always evaluates to `true`. This happens when both sides of the operator are always truthy, making the condition constant. In this example, `$one` is always `1` (truthy) and the result of the entire `&&` expression is always `true` when used outside a first-level statement. This usually indicates a logic error, a redundant check, or dead code.

## How to fix it

Remove the redundant operand if the condition is always true:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$one = 1;
-	$result = $one && $i;
+	$result = (bool) $i;
 }
```

Or fix the logic to use the correct variable so the result is not always the same:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$one = 1;
-	$result = $one && $i;
+	$result = $flag && $i;
 }
```
