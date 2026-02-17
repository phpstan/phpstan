---
title: "assign.byRefForeachExpr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$array = [1, 2, 3];
foreach ($array as &$item) {
	$item *= 2;
}

$item = 'foo';
```

## Why is it reported?

After a `foreach` loop that iterates by reference, the loop variable (`$item`) remains a reference to the last element of the array. Any subsequent assignment to that variable will overwrite the last element of the array, which is almost always unintentional and a common source of bugs in PHP.

In the example above, after the `foreach` loop, `$item` still references `$array[2]`. Assigning `'foo'` to `$item` changes `$array[2]` from `6` to `'foo'`.

## How to fix it

Unset the reference variable immediately after the `foreach` loop:

```diff-php
 <?php declare(strict_types = 1);

 $array = [1, 2, 3];
 foreach ($array as &$item) {
 	$item *= 2;
 }
+unset($item);

 $item = 'foo';
```
