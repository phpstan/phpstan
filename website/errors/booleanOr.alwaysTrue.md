---
title: "booleanOr.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$result = $i >= 0 || $i < 0;
}
```

## Why is it reported?

The result of the `||` (boolean OR) expression always evaluates to `true`. This happens when at least one side of the operator is guaranteed to be truthy regardless of the input. In the example above, every integer is either greater than or equal to zero, or less than zero, so the entire expression is always `true`. This usually indicates a logic error, a redundant check, or dead code.

## How to fix it

Fix the logic to produce a meaningful condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$result = $i >= 0 || $i < 0;
+	$result = $i >= 0;
 }
```

Or remove the redundant expression entirely:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i): true
 {
-	$result = $i >= 0 || $i < 0;
+	return true;
 }
```
