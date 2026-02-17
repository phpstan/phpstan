---
title: "generator.nonIterable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @return \Generator<int> */
function doFoo(): \Generator
{
	$value = 42;
	yield from $value;
}
```

## Why is it reported?

The expression passed to `yield from` is not iterable. `yield from` requires an iterable argument such as an array, a `Traversable`, or another `Generator`. In the example above, `$value` is an `int`, which cannot be iterated.

## How to fix it

Pass an iterable expression to `yield from`:

```diff-php
 <?php declare(strict_types = 1);

 /** @return \Generator<int> */
 function doFoo(): \Generator
 {
-	$value = 42;
-	yield from $value;
+	$values = [42];
+	yield from $values;
 }
```
