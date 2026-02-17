---
title: "attribute.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait OldTrait
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references a trait that has been marked as `@deprecated`. Deprecated types are planned for removal in a future version, and attributes should not rely on them.

## How to fix it

Replace the usage of the deprecated trait with its recommended replacement.
