---
title: "asort.list"
shortDescription: "Sort-has-no-effect variant for asort() called on a list; not emitted in practice."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param list<string> $list */
function sortValues(array $list): void
{
	asort($list);
}
```

## Why is it reported?

This identifier belongs to the family of "sort call has no effect" errors. It would report that `asort()` was called on a value that is always a list where sorting cannot change the result.

In practice `asort()` sorts an array by value in ascending order while preserving the keys, which does change a list with more than one element, so this variant is not emitted. Among the sort functions only [`ksort()`](/error-identifiers/ksort.list) reports the `list` variant, because a list's integer keys are already sorted in ascending order.

## How to fix it

If PHPStan reports this on your code, `asort()` cannot change the given list. Remove the redundant call, or pass the array you actually intend to sort.
