---
title: "equal.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(\stdClass $object, int $number): void
{
	if ($object == $number) {
		// ...
	}
}
```

## Why is it reported?

The loose comparison (`==`) between an object and an integer results in an error in PHP. Some type combinations are not comparable, and attempting to compare them produces a `TypeError` since PHP 8.0. In this example, comparing a `\stdClass` object with an `int` is not a valid operation.

This also applies to other comparison operators like `!=`, `<`, `>`, `<=`, `>=`, and `<=>` when used with incompatible types.

## How to fix it

Compare values of compatible types instead. Extract a comparable value from the object first:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $object, int $number): void
 {
-	if ($object == $number) {
+	if ($object->value === $number) {
 		// ...
 	}
 }
```

Or use strict comparison (`===`) when checking for identity rather than equality, which does not produce an error for incompatible types (it simply returns `false`):

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $object, int $number): void
 {
-	if ($object == $number) {
+	if ($object === $number) {
 		// ...
 	}
 }
```
