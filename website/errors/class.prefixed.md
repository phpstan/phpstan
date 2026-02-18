---
title: "class.prefixed"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$reflection = new _PHPStan_\BetterReflection\Reflection\ReflectionClass('Foo');
```

## Why is it reported?

The code references a class with an internal vendor prefix (such as `_PHPStan_`, `RectorPrefix`, `_PhpScoper`, `PHPUnitPHAR`, or `_HumbugBox`). These prefixes are added by tools like PHP-Scoper during the build process to prevent namespace collisions in PHAR archives. Referencing prefixed class names is almost always unintentional and will break when the tool is updated or when running outside the PHAR context.

## How to fix it

Use the original, unprefixed class name instead:

```diff-php
 <?php declare(strict_types = 1);

-$reflection = new _PHPStan_\BetterReflection\Reflection\ReflectionClass('Foo');
+$reflection = new \BetterReflection\Reflection\ReflectionClass('Foo');
```
