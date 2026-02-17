---
title: "requireImplements.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
enum Suit: string
{
    case Hearts = 'hearts';
    case Diamonds = 'diamonds';
}

/**
 * @phpstan-require-implements Suit
 */
trait SuitTrait {}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references an enum that has been marked as `@deprecated`. Using deprecated symbols in new code perpetuates reliance on APIs that are scheduled for removal or replacement. Since `@phpstan-require-implements` defines a contract for future use, referencing a deprecated enum in it is especially problematic.

## How to fix it

Replace the deprecated enum with its non-deprecated replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements Suit
+ * @phpstan-require-implements NewSuitInterface
  */
 trait SuitTrait {}
```

Note that `@phpstan-require-implements` can only reference interfaces, not enums. If the enum itself is the intended target, the PHPDoc tag should be changed to reference an interface that the enum implements.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.
