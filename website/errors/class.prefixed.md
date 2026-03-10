---
title: "class.prefixed"
shortDescription: "Class name uses an internal vendor prefix from a PHAR build."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function test(_PHPStan_foo\SomeClass $obj): void
{
	echo get_class($obj);
}
```

## Why is it reported?

The code references a class with an internal vendor prefix (such as `_PHPStan_`, `RectorPrefix`, `_PhpScoper`, `PHPUnitPHAR`, or `_HumbugBox`). These prefixes are added by tools like PHP-Scoper during the build process to prevent namespace collisions in PHAR archives. Referencing prefixed class names is almost always unintentional and will break when the tool is updated or when running outside the PHAR context.

## How to fix it

Use the original, unprefixed class name instead:

```diff-php
 <?php declare(strict_types = 1);

-function test(_PHPStan_foo\SomeClass $obj): void
+function test(\SomeClass $obj): void
 {
 	echo get_class($obj);
 }
```
