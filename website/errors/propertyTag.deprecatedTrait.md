---
title: "propertyTag.deprecatedTrait"
shortDescription: "@property PHPDoc tag references a deprecated trait."
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
 * @property OldHelper $helper
 */
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@property` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version. Additionally, traits are not valid types in PHP, so using a deprecated trait in a `@property` tag compounds two issues.

Note: in practice, the `propertyTag.trait` error (reporting that traits are not valid types in `@property`) takes precedence and this deprecation identifier is not reported alongside it.

## How to fix it

Replace the deprecated trait reference in the `@property` tag with a valid, non-deprecated type:

```diff-php
 /**
- * @property OldHelper $helper
+ * @property NewHelper $helper
  */
 class Foo
 {
 }
```
