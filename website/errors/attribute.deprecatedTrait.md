---
title: "attribute.deprecatedTrait"
shortDescription: "Attribute references a deprecated trait (deprecation-rules)."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait OldTrait
{
}

#[OldTrait]
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and attributes should not rely on them.

Note: triggering this identifier requires using a trait as an attribute, which PHP does not support. PHPStan therefore always also reports an `attribute.notAttribute` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Replace the deprecated trait with a proper attribute class:

```diff-php
-#[OldTrait]
+#[NewAttribute]
 class Foo
 {
 }
```
