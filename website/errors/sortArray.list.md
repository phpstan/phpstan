---
title: "sortArray.list"
shortDescription: "Calling ksort on a list has no effect because its keys are already in ascending order."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param list<string> $list */
function doFoo(array $list): void
{
	ksort($list);
}
```

## Why is it reported?

A list is an array whose keys are the consecutive integers `0, 1, 2, …` in ascending order. `ksort()` sorts an array by its keys, but the keys of a list are already sorted, so the call cannot change anything.

This is reported for `ksort()` when the array is a list and the sort flags keep the numeric key order (`SORT_REGULAR` or `SORT_NUMERIC`, which is the default). Such a call usually signals confusion between sorting by keys and sorting by values.

## How to fix it

If you meant to sort the values rather than the keys, use `sort()`:

```diff-php
 /** @param list<string> $list */
 function doFoo(array $list): void
 {
-	ksort($list);
+	sort($list);
 }
```

If the sort is unnecessary, remove it:

```diff-php
 /** @param list<string> $list */
 function doFoo(array $list): void
 {
-	ksort($list);
 }
```

This rule uses the analysed type, which may come from PHPDoc. If you don't want PHPDoc types to be treated as certain here, set [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
