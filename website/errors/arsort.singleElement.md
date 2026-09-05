---
title: "arsort.singleElement"
shortDescription: "Calling arsort() on an array with at most one element, so the call has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param array{foo: int} $array */
function sortValues(array $array): void
{
	arsort($array);
}
```

## Why is it reported?

`arsort()` sorts an array by value in descending order while preserving the keys. An array with at most one element has nothing to reorder, so the call has no effect. Here `$array` is typed as `array{foo: int}`, which always has exactly one element.

## How to fix it

If the array can actually contain more than one element, fix the type so it reflects that:

```diff-php
-/** @param array{foo: int} $array */
+/** @param array<string, int> $array */
 function sortValues(array $array): void
```

Otherwise remove the redundant call:

```diff-php
 function sortValues(array $array): void
 {
-	arsort($array);
 }
```
