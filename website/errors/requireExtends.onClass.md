---
title: "requireExtends.onClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends SomeClass
 */
class InvalidClass
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is placed on a class. This tag is only valid on traits and interfaces. On a trait, it ensures that any class using the trait extends a specific base class. On an interface, it ensures that implementing classes extend a specific base class. Using it on a regular class has no effect and indicates a mistake.

## How to fix it

Remove the `@phpstan-require-extends` tag from the class:

```diff-php
-/**
- * @phpstan-require-extends SomeClass
- */
 class InvalidClass
 {
 }
```

If the constraint is intended, move it to a trait or interface where `@phpstan-require-extends` is valid:

```diff-php
 /**
  * @phpstan-require-extends SomeClass
  */
-class InvalidClass
+interface MyInterface
 {
 }
```
