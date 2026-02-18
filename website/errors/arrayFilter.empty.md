---
title: "arrayFilter.empty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var array{} $empty */
$empty = [];

array_filter($empty);
```

## Why is it reported?

Calling `array_filter()` without a callback on an empty array has no effect. The array is already empty, so there are no elements to filter. This is likely a sign that the variable is not populated as intended, or the `array_filter()` call is unnecessary.

## How to fix it

Remove the unnecessary `array_filter()` call if the array is intentionally empty:

```diff-php
 <?php declare(strict_types = 1);

-$result = array_filter([]);
+$result = [];
```

Or ensure the array is populated before filtering:

```diff-php
 <?php declare(strict_types = 1);

-/** @var array{} $items */
-$items = [];
+/** @var array<int, string|null> $items */
+$items = getItems();

 array_filter($items);
```
