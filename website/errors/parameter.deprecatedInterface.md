---
title: "parameter.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

function doFoo(OldInterface $param): void // ERROR: Parameter $param of function doFoo() has typehint with deprecated interface OldInterface.
{
}
```

## Why is it reported?

This error is reported by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

A function or method parameter uses a deprecated interface as its type declaration. Using deprecated interfaces in parameter types ties your code to interfaces that are planned for removal, making future migration harder.

The error applies to native type declarations on function and method parameters. It is not reported when the function or method itself is already marked as `@deprecated`.

## How to fix it

Replace the deprecated interface with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(OldInterface $param): void
+function doFoo(NewInterface $param): void
 {
 }
```

If you need to support both the old and new interface during a transition period, use a union type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(OldInterface $param): void
+function doFoo(OldInterface|NewInterface $param): void
 {
 }
```
