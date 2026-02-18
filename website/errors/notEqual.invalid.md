---
title: "notEqual.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(stdClass $obj, int $number): void
{
	$result = $obj != $number; // error: Comparison operation "!=" between stdClass and int results in an error.
}
```

## Why is it reported?

PHP does not support loose comparison (`!=`) between certain type combinations. When comparing an object with a scalar type like `int` using `!=`, PHP cannot perform a meaningful comparison and will produce an error. The operation has no well-defined behavior for these types.

## How to fix it

Compare values of the same or compatible types, or extract a comparable value from the object first.

```diff-php
 function doFoo(stdClass $obj, int $number): void
 {
-	$result = $obj != $number;
+	$result = $obj->value !== $number;
 }
```
