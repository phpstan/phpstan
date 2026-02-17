---
title: "foreach.emptyArray"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var array{} $items */
$items = [];

foreach ($items as $item) {
    echo $item;
}
```

## Why is it reported?

The expression passed to `foreach` is an empty array. The loop body will never execute, making it dead code. This usually indicates a logic error where the array was meant to be populated before the loop.

## How to fix it

Make sure the array is populated before iterating, or remove the dead loop:

```diff-php
 <?php declare(strict_types = 1);

-/** @var array{} $items */
-$items = [];
+$items = getItems();

 foreach ($items as $item) {
     echo $item;
 }
```
