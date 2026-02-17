---
title: "requireImplements.deprecatedTrait"
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
 * @phpstan-require-implements OldTrait
 */
trait MyTrait
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references a trait that has been marked with `@deprecated`. Using deprecated symbols leads to issues when they are eventually removed. PHPStan reports this so that the reference can be updated to the recommended replacement.

This error is reported by `phpstan/phpstan-deprecation-rules`.

## How to fix it

Replace the deprecated trait with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements OldTrait
+ * @phpstan-require-implements NewInterface
  */
 trait MyTrait
 {
 }
```
