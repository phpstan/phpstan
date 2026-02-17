---
title: "greaterOrEqual.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(\stdClass $obj, int $n): void
{
	$result = $obj >= $n;
}
```

## Why is it reported?

The `>=` (greater than or equal) comparison operator is being used between two types that cannot be meaningfully compared, resulting in a PHP error. Not all types support comparison operations -- for example, comparing an object to an integer is not a valid operation.

In the example above, a `stdClass` object is compared with an integer using `>=`, which PHP cannot perform.

## How to fix it

Compare values of compatible types, or extract a comparable value from the object first:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $obj, int $n): void
 {
-	$result = $obj >= $n;
+	$result = $obj->value >= $n;
 }
```
