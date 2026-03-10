---
title: "class.implementsDeprecatedClass"
shortDescription: "Class implements a deprecated class."
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

class Foo implements OldService
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class implements a class (not an interface) that has been marked as `@deprecated`. Deprecated classes are planned for removal in a future version.

Note: triggering this identifier requires a class to implement another class, which PHP itself forbids. PHPStan therefore always also reports a non-ignorable `classImplements.class` error alongside this one.

## How to fix it

Implement a proper interface instead of a deprecated class:

```diff-php
-class Foo implements OldService
+class Foo implements NewServiceInterface
 {
 }
```
