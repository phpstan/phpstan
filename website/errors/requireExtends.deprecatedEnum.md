---
title: "requireExtends.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated */
enum Suit: string
{
	case Hearts = 'hearts';
	case Diamonds = 'diamonds';
}

/**
 * @phpstan-require-extends Suit
 */
interface SuitAware
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a deprecated enum. Using deprecated symbols propagates reliance on code that is scheduled for removal or replacement.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated enum with its non-deprecated replacement.

```diff-php
-/** @deprecated */
-enum Suit: string
+enum CardSuit: string
 {
 	case Hearts = 'hearts';
 	case Diamonds = 'diamonds';
 }

 /**
- * @phpstan-require-extends Suit
+ * @phpstan-require-extends CardSuit
  */
 interface SuitAware
 {
 }
```
