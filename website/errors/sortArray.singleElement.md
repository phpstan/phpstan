---
title: "sortArray.singleElement"
shortDescription: "Calling an in-place sort function on an array that has at most one element."
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

In-place sort functions like `ksort()`, `asort()`, `arsort()`, `usort()`, etc. reorder the elements of the array passed to them by reference. An array with at most one element has nothing to reorder, so the call has no effect. This usually indicates a logic error — for example, sorting the wrong variable, or an array type that is narrower than intended.

This check is part of PHPStan's [bleeding edge](/blog/what-is-bleeding-edge) and runs at [rule level](/user-guide/rule-levels) 5.

## How to fix it

Remove the pointless sort call:

```diff-php
 /** @param array{foo: int} $single */
 function doFoo(array $single): void
 {
-	ksort($single);
 }
```

If the array is expected to hold more than one element, correct its type so it reflects that:

```diff-php
-/** @param array{foo: int} $single */
+/** @param array<string, int> $single */
 function doFoo(array $single): void
 {
 	ksort($single);
 }
```

If the array type comes from a PHPDoc that you believe is inaccurate, you can turn off this check by setting [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
