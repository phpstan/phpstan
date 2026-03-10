---
title: "typeAlias.deprecatedTrait"
shortDescription: "Type alias references a deprecated trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLoggable instead */
trait OldLoggable
{
}

/**
 * @phpstan-type LoggerType OldLoggable
 */
class Config
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A type alias defined via `@phpstan-type` references a trait that is marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and type aliases should not depend on them.

Note: traits are not valid types in type aliases. PHPStan therefore always also reports a `typeAlias.trait` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Update the type alias to reference a non-deprecated class or interface:

```diff-php
 /**
- * @phpstan-type LoggerType OldLoggable
+ * @phpstan-type LoggerType NewLoggable
  */
 class Config
 {
 }
```
