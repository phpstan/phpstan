---
title: "paramImmediatelyInvokedCallable.nonCallable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param-immediately-invoked-callable $callback
 */
function doFoo(string $callback): void
{
}
```

## Why is it reported?

The `@param-immediately-invoked-callable` PHPDoc tag is applied to a parameter whose native type is not callable. This tag is used to tell PHPStan that a callable parameter will be invoked immediately within the function (not stored for later use), which affects type narrowing. When the parameter type is not callable, the tag has no meaning.

## How to fix it

Change the parameter type to a callable type:

```diff-php
 /**
  * @param-immediately-invoked-callable $callback
  */
-function doFoo(string $callback): void
+function doFoo(callable $callback): void
 {
 }
```

Or remove the tag if the parameter is not intended to be callable:

```diff-php
-/**
- * @param-immediately-invoked-callable $callback
- */
 function doFoo(string $callback): void
 {
 }
```
