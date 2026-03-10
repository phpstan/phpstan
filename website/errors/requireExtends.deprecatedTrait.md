---
title: "requireExtends.deprecatedTrait"
shortDescription: "@phpstan-require-extends references a deprecated trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

/**
 * @phpstan-require-extends OldHelper
 */
trait MyTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@phpstan-require-extends` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version.

Note: triggering this identifier requires `@phpstan-require-extends` to reference a trait, but this tag expects a class. PHPStan therefore always also reports a `requireExtends.trait` error alongside this one, and in practice the deprecation identifier is not reported.

## How to fix it

Replace the deprecated trait with a proper class reference:

```diff-php
 /**
- * @phpstan-require-extends OldHelper
+ * @phpstan-require-extends NewBaseClass
  */
 trait MyTrait
 {
 }
```
