---
title: "generics.traitBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

/**
 * @template T of MyTrait
 */
class Collection // error: invalid bound type MyTrait
{
}
```

## Why is it reported?

A `@template` tag uses a trait as its bound type or default type. Traits cannot be used as type bounds because they are not standalone types in PHP's type system -- they cannot be used in type declarations, `instanceof` checks, or as parameter/return types. A template type bound must be a class, interface, or a built-in type.

This applies to `@template` tags on classes, interfaces, traits, functions, and methods.

## How to fix it

Replace the trait with an interface or a class as the bound type:

```diff-php
 <?php declare(strict_types = 1);

-trait MyTrait
+interface MyInterface
 {
 }

 /**
- * @template T of MyTrait
+ * @template T of MyInterface
  */
 class Collection
 {
 }
```

If you need to constrain the template type to classes that use a specific trait, consider extracting an interface that the trait's users implement:

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(string $message): void;
}

trait LoggableTrait
{
	public function log(string $message): void
	{
		// ...
	}
}

/**
 * @template T of Loggable
 */
class Collection
{
}
```
