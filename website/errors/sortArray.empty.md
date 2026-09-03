---
title: "sortArray.empty"
shortDescription: "Calling an in-place sort function on an array that is always empty."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$a = [];
	sort($a);
}
```

## Why is it reported?

In-place sort functions like `sort()`, `ksort()`, `asort()`, `usort()`, etc. reorder the elements of the array passed to them by reference. An empty array has no elements to reorder, so the call has no effect. This usually indicates a logic error — the array is sorted before it is populated, or the wrong variable is being sorted.

This check is part of PHPStan's [bleeding edge](/blog/what-is-bleeding-edge) and runs at [rule level](/user-guide/rule-levels) 5.

## How to fix it

Remove the pointless sort call:

```diff-php
 function doFoo(): void
 {
 	$a = [];
-	sort($a);
 }
```

If the array is meant to contain elements, populate it before sorting:

```diff-php
 function doFoo(): void
 {
 	$a = [];
+	$a[] = 'b';
+	$a[] = 'a';
 	sort($a);
 }
```

If the array type comes from a PHPDoc that you believe is inaccurate, you can turn off this check by setting [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
