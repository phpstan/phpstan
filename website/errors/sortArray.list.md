---
title: "sortArray.list"
shortDescription: "Calling ksort() on a list whose keys are already in ascending order."
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

`ksort()` sorts an array by its keys. A list is an array whose keys are consecutive integers starting from `0`, so its keys are already in ascending order. With the default `SORT_REGULAR` flag (or `SORT_NUMERIC`), sorting those keys leaves the array unchanged, so the call has no effect. This usually indicates a logic error or leftover code.

This check is part of PHPStan's [bleeding edge](/blog/what-is-bleeding-edge) and runs at [rule level](/user-guide/rule-levels) 5.

## How to fix it

Remove the pointless sort call:

```diff-php
 /** @param list<string> $list */
 function doFoo(array $list): void
 {
-	ksort($list);
 }
```

If you meant to sort by value instead of by key, use `sort()`:

```diff-php
 /** @param list<string> $list */
 function doFoo(array $list): void
 {
-	ksort($list);
+	sort($list);
 }
```

If the list type comes from a PHPDoc that you believe is inaccurate, you can turn off this check by setting [`treatPhpDocTypesAsCertain: false`](/config-reference#treatphpdoctypesascertain).
