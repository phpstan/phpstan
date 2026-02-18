---
title: "arrayFilter.alwaysEmpty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$values = [null, 0];

$filtered = array_filter($values);
```

## Why is it reported?

When `array_filter()` is called without a callback, PHP removes all falsy values from the array. PHPStan has determined that the array contains only falsy values (such as `0`, `''`, `null`, `false`, or `[]`), so the result will always be an empty array.

In the example above, the array contains `null` and `0`, both of which are falsy, so `array_filter()` will always remove all elements.

## How to fix it

If the filtering is intentional, consider replacing the call with an empty array directly:

```diff-php
 <?php declare(strict_types = 1);

 $values = [null, 0];

-$filtered = array_filter($values);
+$filtered = [];
```

If you intended to filter by a specific condition, provide an explicit callback:

```diff-php
 <?php declare(strict_types = 1);

 $values = [null, 0];

-$filtered = array_filter($values);
+$filtered = array_filter($values, static function ($value): bool {
+    return $value !== null;
+});
```

If the array is expected to contain non-falsy values, fix the type of the source data:

```diff-php
 <?php declare(strict_types = 1);

-$values = [null, 0];
+$values = [1, 2, 3];

 $filtered = array_filter($values);
```
