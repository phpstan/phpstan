---
title: "logicalAnd.leftAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$one = 1;
	if ($one and $i) { // ERROR: Left side of and is always true.
		echo 'entered';
	}
}
```

## Why is it reported?

The left side of the `and` operator always evaluates to `true`. In this example, `$one` is always `1`, which is truthy in PHP, so the left operand of the `and` expression is always `true`. This usually indicates redundant logic, a copy-paste mistake, or a wrong variable being checked. The condition is equivalent to just checking the right side alone.

## How to fix it

Remove the redundant left side if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$one = 1;
-	if ($one and $i) {
+	if ($i) {
 		echo 'entered';
 	}
 }
```

Or fix the logic to use the correct variable:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, int $j): void
 {
-	$one = 1;
-	if ($one and $i) {
+	if ($j and $i) {
 		echo 'entered';
 	}
 }
```
