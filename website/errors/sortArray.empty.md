---
title: "sortArray.empty"
shortDescription: "Calling a sort function on an array that is always empty has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$array = [];
	sort($array);
}
```

## Why is it reported?

Sorting rearranges the elements of an array. An empty array has no elements, so the sort call cannot change anything. PHPStan knows from the type (`array{}`) that the array is always empty, making the call a no-op.

This applies both to key-preserving sort functions (`arsort`, `asort`, `krsort`, `ksort`, `natcasesort`, `natsort`, `uasort`, `uksort`) and to reindexing sort functions (`rsort`, `shuffle`, `sort`, `usort`). Such a call usually signals a logic error — most often the array is filled somewhere else than expected, or the sort was left over from refactoring.

## How to fix it

If the sort is unnecessary, remove it:

```diff-php
 function doFoo(): void
 {
 	$array = [];
-	sort($array);
 }
```

If the array is supposed to contain elements, make sure it is populated before sorting:

```diff-php
 function doFoo(): void
 {
 	$array = [];
+	$array[] = 3;
+	$array[] = 1;
 	sort($array);
 }
```

This rule uses the analysed type, which may come from PHPDoc. If you don't want PHPDoc types to be treated as certain here, set [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
