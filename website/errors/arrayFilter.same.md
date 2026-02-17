---
title: "arrayFilter.same"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var list<positive-int> $numbers */
$numbers = [1, 2, 3];

$filtered = array_filter($numbers);
```

## Why is it reported?

When `array_filter()` is called without a callback, PHP removes all falsy values from the array. PHPStan has determined that the array's value type does not contain any falsy values (such as `0`, `''`, `null`, `false`, or `[]`), so the call has no effect -- the array will always stay the same after filtering.

In the example above, the array contains only positive integers, which are always truthy, so `array_filter()` cannot remove any elements.

## How to fix it

If the filtering is unnecessary, remove the `array_filter()` call:

```diff-php
 <?php declare(strict_types = 1);

 /** @var list<positive-int> $numbers */
 $numbers = [1, 2, 3];

-$filtered = array_filter($numbers);
+$filtered = $numbers;
```

If you intended to filter by a specific condition, provide an explicit callback:

```diff-php
 <?php declare(strict_types = 1);

 /** @var list<positive-int> $numbers */
 $numbers = [1, 2, 3];

-$filtered = array_filter($numbers);
+$filtered = array_filter($numbers, static function (int $value): bool {
+    return $value > 1;
+});
```
