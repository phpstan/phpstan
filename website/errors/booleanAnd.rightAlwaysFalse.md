---
title: "booleanAnd.rightAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i === 0 && $i) {

	}
}
```

## Why is it reported?

The right side of the `&&` (boolean AND) expression always evaluates to `false`. After the left side of `&&` is evaluated, PHPStan narrows the type of the involved variables. If the right-hand side can never be truthy given that narrowed type, the entire expression is always `false`. In the example above, after `$i === 0` passes, `$i` is known to be `0`, which is falsy, so `$i` on the right side is always `false`. This usually indicates a logic error or dead code.

## How to fix it

Fix the logic so that the right-hand side can actually be true:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	if ($i === 0 && $i) {
+	if ($i !== 0 && $i > 5) {

 	}
 }
```

Or remove the expression entirely if the code block is unreachable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	if ($i === 0 && $i) {
-
-	}
 }
```
