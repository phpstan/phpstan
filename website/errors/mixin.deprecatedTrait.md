---
title: "mixin.deprecatedTrait"
shortDescription: "PHPDoc @mixin tag references a deprecated trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void {}
}

/**
 * @mixin OldHelper
 */
class Service
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@mixin` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated types should no longer be used as they may be removed in a future version.

Note: triggering this identifier requires using a trait in a `@mixin` tag, but traits are not valid types in `@mixin`. PHPStan therefore always also reports a `mixin.trait` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Replace the deprecated trait with a class or interface in the `@mixin` tag:

```diff-php
 /**
- * @mixin OldHelper
+ * @mixin NewHelper
  */
 class Service
 {
 }
```
