---
title: "requireExtends.deprecatedInterface"
ignorable: true
---

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

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

The `@phpstan-require-extends` PHPDoc tag references an interface that has been marked as `@deprecated`. Using a deprecated interface in a require-extends constraint means that any class using this trait would be required to extend a deprecated interface, which should be avoided.

## How to fix it

Update the `@phpstan-require-extends` tag to reference the non-deprecated replacement:

```diff-php
 /**
- * @phpstan-require-extends OldInterface
+ * @phpstan-require-extends NewInterface
  */
 trait MyTrait
 {
 }
```
