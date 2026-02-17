---
title: "requireExtends.enum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit: string
{
	case Hearts = 'hearts';
	case Diamonds = 'diamonds';
}

/**
 * @phpstan-require-extends Suit
 */
trait CardTrait // ERROR: PHPDoc tag @phpstan-require-extends cannot contain non-class type Suit.
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is used on traits and interfaces to require that any class using the trait (or implementing the interface) must extend a specific class. However, this tag can only reference a class -- not an enum, interface, or trait.

Enums cannot be extended in PHP, so requiring a class to extend an enum is impossible and makes no sense.

## How to fix it

Replace the enum reference with a class, or use `@phpstan-require-implements` if the intent is to require implementing an interface:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-require-extends Suit
- */
+/**
+ * @phpstan-require-implements HasSuit
+ */
 trait CardTrait
 {
 }
```

If the trait should only be used by classes extending a specific parent class:

```diff-php
 <?php declare(strict_types = 1);

+abstract class Card
+{
+}
+
 /**
- * @phpstan-require-extends Suit
+ * @phpstan-require-extends Card
  */
 trait CardTrait
 {
 }
```
