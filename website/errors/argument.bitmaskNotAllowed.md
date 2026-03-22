---
title: "argument.bitmaskNotAllowed"
shortDescription: "Combining constants with | for a parameter that accepts only a single constant value."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = [3, 1, 2];
$unique = array_unique($a, SORT_REGULAR | SORT_NUMERIC);
```

## Why is it reported?

The `$flags` parameter of `array_unique()` accepts a single sorting constant, not a bitmask of combined constants. Combining `SORT_REGULAR | SORT_NUMERIC` with the `|` operator produces an integer value that does not correspond to any valid flag, leading to unexpected behavior.

Some PHP functions accept bitmask parameters where multiple flags can be combined (like `json_encode()`), while others expect exactly one constant value. PHPStan knows which parameters allow bitmasks and which do not.

This check is only performed on PHP 8.0+.

## How to fix it

Pass a single constant without combining:

```diff-php
 $a = [3, 1, 2];
-$unique = array_unique($a, SORT_REGULAR | SORT_NUMERIC);
+$unique = array_unique($a, SORT_NUMERIC);
```
