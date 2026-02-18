---
title: "generics.deprecatedTraitBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait OldTrait
{
}

/**
 * @template T of OldTrait
 */
class Foo
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@template` bound references a trait that has been marked as `@deprecated`. Deprecated types should no longer be used as they may be removed in a future version.

## How to fix it

Replace the deprecated trait with the recommended replacement in the template bound:

```diff-php
 /**
- * @template T of OldTrait
+ * @template T of NewTrait
  */
 class Foo
 {
 }
```
