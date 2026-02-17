---
title: "parameter.unresolvableNativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(Countable&Traversable&int $value): void // ERROR: Parameter $value has unresolvable native type.
{
}
```

## Why is it reported?

The parameter's native type declaration is unresolvable, meaning it creates a type that cannot exist at runtime. This typically happens when an intersection type (`&`) combines types that are fundamentally incompatible, such as an object type and a scalar type. PHP's intersection types (available since PHP 8.1) require all members to be class or interface types, and the resulting type must be possible to satisfy.

Since this is a PHP language constraint, the code will fail at runtime.

## How to fix it

Fix the type declaration to use a valid combination of types:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(Countable&Traversable&int $value): void
+function doFoo(Countable&Traversable $value): void
 {
 }
```

Or use a union type if multiple unrelated types should be accepted:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(Countable&Traversable&int $value): void
+function doFoo(Countable&Traversable|int $value): void
 {
 }
```
