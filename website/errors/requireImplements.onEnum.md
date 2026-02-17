---
title: "requireImplements.onEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface SomeInterface
{
}

/**
 * @phpstan-require-implements SomeInterface
 */
enum Suit
{
    case Hearts;
    case Diamonds;
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is only valid on traits. It is used to declare that any class using the trait must implement a specific interface. Placing it on an enum (or a class) has no effect and is a mistake.

Enums cannot be extended, so the concept of requiring that a using class implements a specific interface does not apply.

## How to fix it

Remove the `@phpstan-require-implements` tag from the enum. If the enum should implement an interface, declare it directly:

```diff-php
 <?php declare(strict_types = 1);

 interface SomeInterface
 {
 }

-/**
- * @phpstan-require-implements SomeInterface
- */
-enum Suit
+enum Suit implements SomeInterface
 {
     case Hearts;
     case Diamonds;
 }
```

Or move the tag to a trait where it is valid:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-implements SomeInterface
  */
 trait SomeTrait
 {
 }
```
