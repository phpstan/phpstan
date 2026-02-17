---
title: "arrayValues.empty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$result = array_values([]);
```

## Why is it reported?

The call to `array_values()` is made on an array that is always empty. Since `array_values()` re-indexes an array's values with consecutive integer keys starting from `0`, calling it on an empty array has no effect and always returns an empty array. This is dead code that serves no purpose.

## How to fix it

Remove the unnecessary `array_values()` call:

```diff-php
 <?php declare(strict_types = 1);

-$result = array_values([]);
+$result = [];
```

Or if the variable was expected to contain elements, fix the upstream logic so the array is not always empty:

```php
<?php declare(strict_types = 1);

/** @var array<string, int> $data */
$data = getData();

$result = array_values($data);
```
