---
title: "requireExtends.deprecatedEnum"
shortDescription: "@phpstan-require-extends references a deprecated enum."
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
}

/**
 * @phpstan-require-extends OldStatus
 */
interface StatusAware
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@phpstan-require-extends` PHPDoc tag references a deprecated enum. Deprecated enums are planned for removal in a future version.

Note: triggering this identifier requires `@phpstan-require-extends` to reference an enum, which is not valid. PHPStan therefore always also reports a `requireExtends.enum` error, and in practice the deprecation identifier is not reported alongside it.

## How to fix it

Replace the deprecated enum with a proper class reference:

```diff-php
 /**
- * @phpstan-require-extends OldStatus
+ * @phpstan-require-extends NewBaseClass
  */
 interface StatusAware
 {
 }
```
