---
title: "usort.singleElement"
shortDescription: "Calling usort() on an array with at most one element, so the call has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param array{int} $array */
function sortValues(array $array): void
{
	usort($array, fn ($a, $b) => 0);
}
```

## Why is it reported?

`usort()` sorts an array by value using a user-defined comparison callback and reindexes it. An array with at most one element has nothing to reorder, so the call has no effect. Here `$array` is typed as `array{int}`, which always has exactly one element.

## How to fix it

If the array can actually contain more than one element, fix the type so it reflects that:

```diff-php
-/** @param array{int} $array */
+/** @param list<int> $array */
 function sortValues(array $array): void
```

Otherwise remove the redundant call:

```diff-php
 function sortValues(array $array): void
 {
-	usort($array, fn ($a, $b) => 0);
 }
```
