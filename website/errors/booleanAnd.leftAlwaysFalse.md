---
title: "booleanAnd.leftAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$zero = 0;
	if ($zero && $i) {
		// never reached
	}
}
```

## Why is it reported?

The left side of the `&&` (boolean AND) expression always evaluates to `false`. Because `&&` uses short-circuit evaluation, when the left operand is always falsy, the right operand is never evaluated, and the entire expression is always `false`. This means the condition body will never be executed, indicating a logic error or redundant code.

In the example above, `$zero` is always `0`, which is falsy in PHP, so `$zero && $i` is always `false`.

## How to fix it

Remove the redundant condition if the code inside should never execute:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$zero = 0;
-	if ($zero && $i) {
-		// never reached
-	}
 }
```

Or fix the logic to check the correct variable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$zero = 0;
-	if ($zero && $i) {
+	if ($i && $i > 0) {
 		// now depends on actual input
 	}
 }
```
