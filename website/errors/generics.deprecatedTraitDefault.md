---
title: "generics.deprecatedTraitDefault"
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
 * @template T = OldTrait
 */
class Collection
{
}
```

## Why is it reported?

The `@template` tag default value references a trait that is marked as `@deprecated`. Using deprecated types in generic type defaults propagates the dependency on outdated code to all consumers that rely on the default. This rule is provided by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

## How to fix it

Replace the deprecated trait with its non-deprecated replacement in the template default:

```diff-php
 /**
- * @template T = OldTrait
+ * @template T = NewTrait
  */
 class Collection
 {
 }
```
