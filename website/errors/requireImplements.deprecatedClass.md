---
title: "requireImplements.deprecatedClass"
shortDescription: "@phpstan-require-implements references a deprecated class."
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

/**
 * @phpstan-require-implements OldService
 */
trait MyTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@phpstan-require-implements` PHPDoc tag references a class that has been marked as `@deprecated`. Deprecated classes are planned for removal in a future version.

Note: triggering this identifier requires `@phpstan-require-implements` to reference a class, but this tag expects an interface. PHPStan therefore always also reports a `requireImplements.class` error alongside this one, and in practice the deprecation identifier is not reported.

## How to fix it

Replace the deprecated class with a proper interface reference:

```diff-php
 /**
- * @phpstan-require-implements OldService
+ * @phpstan-require-implements NewServiceInterface
  */
 trait MyTrait
 {
 }
```
