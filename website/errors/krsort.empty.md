---
title: "krsort.empty"
shortDescription: "Calling krsort() on an array that is always empty, so the call has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$array = [];
krsort($array);
```

## Why is it reported?

The array passed to `krsort()` is always empty. Sorting an empty array produces the same empty array, so the call does nothing. This usually points to a logic error — for example, the array is sorted before it is ever filled, or the wrong variable is passed.

## How to fix it

If the array is meant to hold elements, sort it after it has been populated:

```diff-php
 $array = [];
+$array[] = 'a';
+$array[] = 'b';
 krsort($array);
```

Otherwise remove the redundant call:

```diff-php
 $array = [];
-krsort($array);
```
