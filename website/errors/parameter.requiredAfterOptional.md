---
title: "parameter.requiredAfterOptional"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a = 1, string $b): void
{
}
```

## Why is it reported?

A required parameter follows an optional parameter. This has been deprecated in PHP 8.0 and will cause a deprecation notice at runtime. The optional parameter's default value can never actually be used because the required parameter after it forces the caller to always provide all arguments up to that point.

Since PHP 8.1, this also applies to nullable parameters with a `null` default value (e.g., `?int $foo = null`).

## How to fix it

Reorder the parameters so that required parameters come before optional ones:

```diff-php
-function doFoo(int $a = 1, string $b): void
+function doFoo(string $b, int $a = 1): void
 {
 }
```

Alternatively, make the first parameter required if the default value is not needed:

```diff-php
-function doFoo(int $a = 1, string $b): void
+function doFoo(int $a, string $b): void
 {
 }
```

If both parameters should be optional, give the second one a default value too:

```diff-php
-function doFoo(int $a = 1, string $b): void
+function doFoo(int $a = 1, string $b = ''): void
 {
 }
```
