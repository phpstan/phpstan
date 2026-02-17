---
title: "requireImplements.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
}

/**
 * @phpstan-require-implements Loggable
 */
interface AnotherInterface // ERROR: PHPDoc tag @phpstan-require-implements is only valid on trait.
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is only valid on traits. It allows a trait to declare that any class using it must implement a specific interface. When this tag is placed on an interface (or a class, or an enum), PHPStan reports an error because it is not a valid location for this tag.

Interfaces already have their own mechanism for requiring implementations -- they can extend other interfaces directly using the `extends` keyword.

## How to fix it

If the goal is to have one interface require another, use `extends` instead:

```diff-php
 <?php declare(strict_types = 1);

 interface Loggable
 {
 }

-/**
- * @phpstan-require-implements Loggable
- */
-interface AnotherInterface
+interface AnotherInterface extends Loggable
 {
 }
```

If the tag is intended for a trait, move it to the trait:

```diff-php
 <?php declare(strict_types = 1);

 interface Loggable
 {
 }

 /**
  * @phpstan-require-implements Loggable
  */
-interface AnotherInterface
-{
-}
+trait LoggableTrait
+{
+}
```
