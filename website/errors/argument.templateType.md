---
title: "argument.templateType"
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
 * @template T
 * @return T
 */
function broken(): mixed
{
    return null;
}

function doFoo(): void
{
    // T cannot be resolved because there is no argument
    // that would allow PHPStan to infer the template type
    broken();
}
```

## Why is it reported?

The called function or method declares a template type (generic type parameter) that appears in the return type, but PHPStan cannot determine what concrete type it should resolve to based on the provided arguments. This typically happens when the template type is used in the return type but none of the parameters allow PHPStan to infer it from the call site.

Learn more: [Solving PHPStan error "Unable to resolve template type"](/blog/solving-phpstan-error-unable-to-resolve-template-type)

## How to fix it

Pass an argument that allows PHPStan to infer the template type:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
- * @return T
+ * @param class-string<T> $class
+ * @return T
  */
-function broken(): mixed
+function create(string $class): mixed
 {
-    return null;
+    return new $class();
 }
```

Or specify the template type explicitly using PHPDoc:

```diff-php
-broken();
+/** @var string $result */
+$result = broken();
```
