---
title: "attribute.deprecatedEnum"
shortDescription: "Attribute references a deprecated enum (deprecation-rules)."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

#[OldStatus]
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references an enum that has been marked as `@deprecated`. Deprecated enums are planned for removal in a future version, and attributes should not rely on them.

Note: triggering this identifier requires using an enum as an attribute, which PHP does not support. PHPStan therefore always also reports an `attribute.notAttribute` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Replace the usage of the deprecated enum with a proper attribute class:

```diff-php
-#[OldStatus]
+#[NewStatusAttribute]
 class Foo
 {
 }
```
