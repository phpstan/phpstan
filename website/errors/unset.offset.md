---
title: "unset.offset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$scalar = 3;
	unset($scalar['a']);
}
```

## Why is it reported?

The `unset()` call attempts to remove an array offset from a type that either does not support offset access or does not have the specified offset. This indicates a logic error -- the variable is not the array that was expected, or the offset does not exist on the given type.

In the example above, `$scalar` is an `int`, which does not support offset access. Attempting to unset an offset on it is meaningless.

## How to fix it

Make sure `unset()` is called on a variable that supports offset access and has the given offset:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$scalar = 3;
-	unset($scalar['a']);
+	$data = ['a' => 1, 'b' => 2];
+	unset($data['a']);
 }
```

Or verify the type of the variable before unsetting:

```diff-php
 <?php declare(strict_types = 1);

-/** @param iterable<int, int> $iterable */
-function doBar(iterable $iterable): void
+/** @param array<int, int> $data */
+function doBar(array $data): void
 {
-	unset($iterable['string']);
+	unset($data[0]);
 }
```
