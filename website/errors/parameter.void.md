---
title: "parameter.void"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(void $param): void // ERROR: Parameter $param has typehint with void.
{
}
```

## Why is it reported?

The `void` type cannot be used as a parameter type in PHP. The `void` type is only valid as a return type, where it indicates that the function does not return a value. Using `void` as a parameter type is a language-level error that will cause a fatal error at runtime.

## How to fix it

Use the appropriate type for the parameter. If the parameter is intended to accept no meaningful value, consider whether the parameter is needed at all:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(void $param): void
+function doFoo(): void
 {
 }
```

If the parameter should accept any type including `null`, use `mixed`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(void $param): void
+function doFoo(mixed $param): void
 {
 }
```
