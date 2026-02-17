---
title: "booleanOr.leftAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$zero = 0;
	if ($zero || $i > 0) {
		// ...
	}
}
```

## Why is it reported?

The left side of the `||` (boolean OR) expression always evaluates to `false`. While the overall expression might still be `true` depending on the right side, having a left operand that is always falsy indicates dead code or a logic error. The left operand serves no purpose because the result of the `||` expression is entirely determined by the right side.

In the example above, `$zero` is always `0`, which is falsy in PHP, so the left side of `||` is always `false`.

## How to fix it

Remove the redundant left operand:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$zero = 0;
-	if ($zero || $i > 0) {
+	if ($i > 0) {
 		// ...
 	}
 }
```

Or fix the logic to use the correct variable:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$zero = 0;
-	if ($zero || $i > 0) {
+	if ($flag || $i > 0) {
 		// ...
 	}
 }
```
