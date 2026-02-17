---
title: "arrayUnpacking.stringOffset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = [1, 2, 3];
$b = ['key' => 'value'];

$merged = [...$a, ...$b];
```

## Why is it reported?

Before PHP 8.1, array unpacking with the spread operator (`...`) only supported arrays with integer keys. Using array unpacking on an array with string keys results in a fatal error in PHP versions prior to 8.1.

In the example above, `$b` has the string key `'key'`, so unpacking it with `...$b` is not supported on the configured PHP version.

## How to fix it

Use `array_merge()` instead of array unpacking when dealing with string-keyed arrays:

```diff-php
 <?php declare(strict_types = 1);

 $a = [1, 2, 3];
 $b = ['key' => 'value'];

-$merged = [...$a, ...$b];
+$merged = array_merge($a, $b);
```

Or upgrade the project's PHP version to 8.1 or later, which supports array unpacking with string keys. Update the [`phpVersion`](https://phpstan.org/config-reference#phpversion) setting in the PHPStan configuration accordingly.
