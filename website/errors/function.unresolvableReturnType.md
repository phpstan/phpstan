---
title: "function.unresolvableReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @param T $value
 * @return T
 */
function identity(mixed $value): mixed
{
    return $value;
}

/**
 * @template T of object
 * @param class-string<T> $class
 * @return T
 */
function create(string $class): object
{
    return new $class();
}

function doFoo(): void
{
    $result = create(identity('stdClass'));
}
```

## Why is it reported?

The return type of a function call contains an unresolvable type. This happens when a function's return type depends on generic template types that PHPStan cannot fully resolve from the provided arguments. The result is that PHPStan cannot determine the precise return type, which may lead to less accurate analysis downstream.

This typically occurs when generic functions are composed in complex ways, or when the type information flowing through nested function calls is insufficient for PHPStan to resolve all template types.

## How to fix it

Break the expression into separate steps so PHPStan can resolve the types at each stage:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    $result = create(identity('stdClass'));
+    $className = identity('stdClass');
+    $result = create($className);
 }
```

Or provide explicit type annotations to help PHPStan understand the types:

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
    /** @var class-string<\stdClass> $className */
    $className = identity('stdClass');
    $result = create($className);
}
```
