---
title: "sort.empty"
shortDescription: "Calling sort() on an array that is always empty, so the call has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$array = [];
sort($array);
```

## Why is it reported?

The array passed to `sort()` is always empty. Sorting an empty array produces the same empty array, so the call does nothing. This usually points to a logic error — for example, the array is sorted before it is ever filled, or the wrong variable is passed.

## How to fix it

If the array is meant to hold elements, sort it after it has been populated:

```diff-php
 $array = [];
+$array[] = 'a';
+$array[] = 'b';
 sort($array);
```

Otherwise remove the redundant call:

```diff-php
 $array = [];
-sort($array);
```
