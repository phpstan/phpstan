---
title: "greater.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(\stdClass $object, int $number): void
{
	if ($object > $number) {
		// ...
	}
}
```

## Why is it reported?

The greater-than comparison (`>`) between an object and an integer results in an error in PHP. Since PHP 8.0, comparing incompatible types with the `>` operator throws a `TypeError`. In this example, comparing a `\stdClass` object with an `int` is not a valid operation.

## How to fix it

Compare values of compatible types instead. Extract a comparable value from the object first:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $object, int $number): void
 {
-	if ($object > $number) {
+	if ($object->value > $number) {
 		// ...
 	}
 }
```

Or ensure both operands are of comparable types:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(\stdClass $object, int $number): void
+function doFoo(int $a, int $number): void
 {
-	if ($object > $number) {
+	if ($a > $number) {
 		// ...
 	}
 }
```
