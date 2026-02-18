---
title: "requireExtends.class"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends SomeClass
 */
class InvalidUsage // ERROR: PHPDoc tag @phpstan-require-extends is only valid on trait or interface.
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is used on a class, but this tag is only valid on traits and interfaces. The tag is designed to constrain which classes can use a trait or implement an interface by requiring them to extend a specific base class. Using it on a class itself does not make sense because a class already defines its own inheritance.

## How to fix it

If the intent is to constrain trait users, move the tag to a trait:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-extends SomeClass
  */
-class InvalidUsage
+trait RequiresSomeClass
 {
 }
```

If the intent is to constrain interface implementations, move the tag to an interface:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-require-extends SomeClass
  */
-class InvalidUsage
+interface RequiresSomeClass
 {
 }
```

If the class should simply extend the specified class, use the `extends` keyword:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-require-extends SomeClass
- */
-class InvalidUsage
+class ValidUsage extends SomeClass
 {
 }
```
