---
title: "new.deprecatedInterface"
shortDescription: "Instantiation of a deprecated interface."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

$x = new OldInterface();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A deprecated interface is used in a `new` expression. Deprecated interfaces are planned for removal in a future version.

Note: triggering this identifier requires instantiating an interface, which PHP does not allow. PHPStan therefore always also reports a `new.interface` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Instantiate a concrete class that implements the replacement interface:

```diff-php
-$x = new OldInterface();
+$x = new ConcreteImplementation();
```
