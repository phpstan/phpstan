---
title: "attribute.deprecatedInterface"
shortDescription: "Attribute references a deprecated interface (deprecation-rules)."
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

#[OldInterface]
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references an interface that has been marked as `@deprecated`. Deprecated interfaces are planned for removal in a future version, and attributes should not rely on them.

Note: triggering this identifier requires using an interface as an attribute, which PHP does not support. PHPStan therefore always also reports an `attribute.notAttribute` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Replace the deprecated interface with a proper attribute class:

```diff-php
-#[OldInterface]
+#[NewAttribute]
 class Foo
 {
 }
```
