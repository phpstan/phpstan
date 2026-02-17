---
title: "arrayValues.list"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var list<string> $names */
$names = ['Alice', 'Bob', 'Charlie'];

$result = array_values($names);
```

## Why is it reported?

The `array_values()` function returns a list of all values in the array, re-indexed with consecutive integer keys starting from 0. PHPStan has determined that the argument is already a list (an array with consecutive integer keys starting from 0), so calling `array_values()` has no effect -- the result will be identical to the input.

In the example above, `$names` is typed as `list<string>`, which already has sequential integer keys. Calling `array_values()` on it produces the same array.

## How to fix it

If the call is unnecessary, remove it:

```diff-php
 <?php declare(strict_types = 1);

 /** @var list<string> $names */
 $names = ['Alice', 'Bob', 'Charlie'];

-$result = array_values($names);
+$result = $names;
```

If you are calling `array_values()` to narrow the type from `array` to `list`, fix the type of the source variable instead so that it is already known to be a list.
