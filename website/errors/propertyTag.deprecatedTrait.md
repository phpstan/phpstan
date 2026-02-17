---
title: "propertyTag.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use SomethingElse instead */
trait HelperTrait
{
}

/**
 * @property HelperTrait $helper // ERROR: PHPDoc tag @property references deprecated trait HelperTrait.
 */
class Foo
{
}
```

## Why is it reported?

The `@property` PHPDoc tag references a trait that has been marked as `@deprecated`. Traits are not valid types in PHP, and using a deprecated trait as a type in a `@property` tag compounds two issues: the type is not valid, and the referenced trait is deprecated.

This rule is provided by the [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated trait reference in the `@property` tag with a valid, non-deprecated type such as an interface or class:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @property HelperTrait $helper
+ * @property HelperInterface $helper
  */
 class Foo
 {
 }
```
