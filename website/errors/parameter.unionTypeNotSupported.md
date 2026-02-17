---
title: "parameter.unionTypeNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int|string $value): void // ERROR: This function uses native union types but they're supported only on PHP 8.0 and later.
{
}
```

## Why is it reported?

Native union type syntax (`int|string`) for parameters was introduced in PHP 8.0. When PHPStan is configured to analyse code for a PHP version earlier than 8.0 (via the [`phpVersion`](/config-reference#phpversion) configuration parameter), using native union types in parameter declarations is a syntax error. The code will not run on the targeted PHP version.

## How to fix it

If the code needs to run on PHP versions before 8.0, use PHPDoc to document the union type instead of the native syntax:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int|string $value): void
+/**
+ * @param int|string $value
+ */
+function doFoo($value): void
 {
 }
```

Or update the [`phpVersion`](/config-reference#phpversion) configuration in `phpstan.neon` if the project targets PHP 8.0 or later:

```neon
parameters:
    phpVersion: 80000
```
