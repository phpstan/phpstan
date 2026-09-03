---
title: "sort.list"
shortDescription: "Sort-has-no-effect variant for sort() called on a list; not emitted in practice."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param list<string> $list */
function sortValues(array $list): void
{
	sort($list);
}
```

## Why is it reported?

This identifier belongs to the family of "sort call has no effect" errors. It would report that `sort()` was called on a value that is always a list where sorting cannot change the result.

In practice `sort()` sorts an array by value in ascending order and reindexes it, which does change a list with more than one element, so this variant is not emitted. Among the sort functions only [`ksort()`](/error-identifiers/ksort.list) reports the `list` variant, because a list's integer keys are already sorted in ascending order.

## How to fix it

If PHPStan reports this on your code, `sort()` cannot change the given list. Remove the redundant call, or pass the array you actually intend to sort.
