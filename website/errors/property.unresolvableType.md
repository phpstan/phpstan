---
title: "property.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var Foo&Bar */
	private $prop;
}
```

## Why is it reported?

The `@var` PHPDoc tag (or PHPDoc type on a promoted property) for a class property contains a type that PHPStan cannot resolve. This typically happens when the type is an intersection of incompatible types, references a misspelled class, or uses an invalid type syntax that produces a type equivalent to `never` (also known as an empty intersection type).

A property with an unresolvable type cannot hold any value, which indicates a mistake in the PHPDoc annotation.

## How to fix it

Correct the PHPDoc type so it references valid, compatible types:

```diff-php
 class Foo
 {
-	/** @var Foo&Bar */
+	/** @var Foo */
 	private $prop;
 }
```

If the property should accept multiple types, use a union type instead of an intersection:

```diff-php
 class Foo
 {
-	/** @var Foo&Bar */
+	/** @var Foo|Bar */
 	private $prop;
 }
```
