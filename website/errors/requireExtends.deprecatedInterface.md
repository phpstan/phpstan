---
title: "requireExtends.deprecatedInterface"
shortDescription: "@phpstan-require-extends references a deprecated interface."
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

/**
 * @phpstan-require-extends OldInterface
 */
trait MyTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@phpstan-require-extends` PHPDoc tag references an interface that has been marked as `@deprecated`. Deprecated interfaces are planned for removal in a future version.

Note: triggering this identifier requires `@phpstan-require-extends` to reference an interface, but this tag expects a class. PHPStan therefore always also reports a `requireExtends.interface` error alongside this one, and in practice the deprecation identifier is not reported.

## How to fix it

Update the `@phpstan-require-extends` tag to reference a non-deprecated class:

```diff-php
 /**
- * @phpstan-require-extends OldInterface
+ * @phpstan-require-extends NewBaseClass
  */
 trait MyTrait
 {
 }
```
