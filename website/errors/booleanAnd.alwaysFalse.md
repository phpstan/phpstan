---
title: "booleanAnd.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$result = $i > 5 && $i < 3;
}
```

## Why is it reported?

The result of the `&&` (boolean AND) expression always evaluates to `false`. This happens when it is impossible for both sides of the operator to be truthy at the same time. In the example above, `$i` cannot be both greater than `5` and less than `3` simultaneously, so the entire expression is always `false`. This usually indicates a logic error or dead code.

## How to fix it

Fix the logic so that both conditions can be satisfied:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$result = $i > 5 && $i < 3;
+	$result = $i > 3 && $i < 5;
 }
```

Or remove the expression entirely if the code block is unreachable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$result = $i > 5 && $i < 3;
 }
```
