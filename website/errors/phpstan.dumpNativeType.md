---
title: "phpstan.dumpNativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

\PHPStan\dumpNativeType(1 + 1);
```

## Why is it reported?

The `PHPStan\dumpNativeType()` function is a debugging tool that outputs the native type of an expression as understood by PHPStan, without taking PHPDoc types into account. It is intentionally reported as an error so the type information appears in the analysis output.

This function is meant for temporary debugging during development. It behaves like `PHPStan\dumpType()` but only considers native PHP type information, ignoring any PHPDoc annotations.

## How to fix it

Remove the `dumpNativeType()` call once the type information is no longer needed:

```diff-php
-\PHPStan\dumpNativeType(1 + 1);
```
