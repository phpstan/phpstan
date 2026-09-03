---
title: "sortArray.noop"
shortDescription: "Calling a sort function on an array that has at most one element has no effect."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param array{foo: int} $single */
function doFoo(array $single): void
{
	ksort($single);
}
```

## Why is it reported?

Sorting only rearranges elements relative to each other. An array with zero or one element has nothing to rearrange, so the call cannot change it. PHPStan knows from the type (here `array{foo: int}`, which always has exactly one element) that the array has at most one element, making the sort call a no-op.

This applies both to key-preserving sort functions (`arsort`, `asort`, `krsort`, `ksort`, `natcasesort`, `natsort`, `uasort`, `uksort`) and to reindexing sort functions (`rsort`, `shuffle`, `sort`, `usort`). Such a call usually signals a logic error — most often the array type is narrower than intended, or the sort was left over from refactoring.

## How to fix it

If the sort is genuinely unnecessary, remove it:

```diff-php
 /** @param array{foo: int} $single */
 function doFoo(array $single): void
 {
-	ksort($single);
 }
```

If the array is meant to hold more than one element, correct the type so it reflects that:

```diff-php
-/** @param array{foo: int} $single */
+/** @param array<string, int> $single */
 function doFoo(array $single): void
 {
 	ksort($single);
 }
```

This rule uses the analysed type, which may come from PHPDoc. If you don't want PHPDoc types to be treated as certain here, set [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
