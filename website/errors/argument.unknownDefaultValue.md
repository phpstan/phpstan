---
title: "argument.unknownDefaultValue"
shortDescription: "An optional parameter whose default value is not known is skipped, so PHP cannot fill it in."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$number = mt_rand(max: 5);
```

## Why is it reported?

Some built-in PHP functions and methods have optional parameters without a default value that PHP can use when the parameter is skipped. For example, `mt_rand()` accepts either no arguments or both `$min` and `$max`. There is no default value for `$min`.

When a named argument skips such a parameter, PHP throws an `ArgumentCountError` at runtime: `mt_rand(): Argument #1 ($min) must be passed explicitly, because the default value is not known`. The same applies to:

- Named arguments that skip such a parameter in a direct call, e.g. `array_keys($array, strict: true)` skips `$filter_value`.
- Calling a closure created from such a function, e.g. `(mt_rand(...))(max: 5)`.
- A [partial function application](https://wiki.php.net/rfc/partial_function_application_v2) (PHP 8.6+) where the variadic placeholder `...` would leave such a parameter to its default, e.g. `$reflectionProperty->setValue(?, ...)`.

## How to fix it

Pass the skipped argument explicitly:

```diff-php
 <?php declare(strict_types = 1);

-$number = mt_rand(max: 5);
+$number = mt_rand(0, 5);
```

In a partial function application, cover the parameter with a `?` placeholder instead of leaving it to `...`:

```diff-php
-$setter = $reflectionProperty->setValue(?, ...);
+$setter = $reflectionProperty->setValue(?, ?);
```
