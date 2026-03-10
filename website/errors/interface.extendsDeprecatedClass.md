---
title: "interface.extendsDeprecatedClass"
shortDescription: "Interface extends a deprecated class."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewService instead */
class OldService
{
}

interface MyInterface extends OldService
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An interface extends a class that has been marked as `@deprecated`. Deprecated classes are planned for removal in a future version.

Note: triggering this identifier requires an interface to extend a class, which PHP itself forbids. PHPStan therefore always also reports a non-ignorable `interfaceExtends.class` error alongside this one.

## How to fix it

Extend a proper interface instead of a deprecated class:

```diff-php
-interface MyInterface extends OldService
+interface MyInterface extends NewServiceInterface
 {
 }
```
