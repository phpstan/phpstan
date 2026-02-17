---
title: "logicalAnd.rightAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$zero = 0;
	if ($i and $zero) { // ERROR: Right side of and is always false.
		echo 'entered';
	}
}
```

## Why is it reported?

The right side of the `and` operator always evaluates to `false`. In this example, `$zero` is always `0`, which is falsy in PHP, so the right operand of the `and` expression is always `false`. This means the entire `and` expression is always `false`, and the code inside the `if` block will never execute. This usually indicates a logic error or a redundant check.

## How to fix it

Remove the redundant right side or fix the variable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$zero = 0;
-	if ($i and $zero) {
+	if ($i) {
 		echo 'entered';
 	}
 }
```

Or fix the logic to check the correct variable:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, int $j): void
 {
-	$zero = 0;
-	if ($i and $zero) {
+	if ($i and $j) {
 		echo 'entered';
 	}
 }
```
