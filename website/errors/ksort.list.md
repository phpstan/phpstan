---
title: "ksort.list"
shortDescription: "Calling ksort() on a list, whose integer keys are already in ascending order, so the call has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param list<string> $list */
function sortKeys(array $list): void
{
	ksort($list);
}
```

## Why is it reported?

`ksort()` sorts an array by its keys in ascending order. A list always has consecutive integer keys `0, 1, 2, …`, which are already in ascending order, so sorting by key leaves it unchanged.

This applies with the default `SORT_REGULAR` flags and with `SORT_NUMERIC`. Other flags such as `SORT_STRING` may order the keys differently, so calls using them are not reported.

## How to fix it

If you meant to sort the values instead of the keys, use `sort()` (which reindexes the array) or `asort()` (which keeps the keys):

```diff-php
-	ksort($list);
+	sort($list);
```

If the sorting is unnecessary, remove the call:

```diff-php
 function sortKeys(array $list): void
 {
-	ksort($list);
 }
```
