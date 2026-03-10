---
title: "sealed.deprecatedTrait"
shortDescription: "@phpstan-sealed references a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait DeprecatedTrait {}

/**
 * @phpstan-sealed DeprecatedTrait
 */
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@phpstan-sealed` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and sealed constraints should not depend on them.

## How to fix it

Replace the deprecated trait reference with its non-deprecated replacement:

```diff-php
 /**
- * @phpstan-sealed DeprecatedTrait
+ * @phpstan-sealed NewTrait
  */
 class Foo
 {
 }
```
