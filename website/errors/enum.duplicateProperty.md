---
title: "enum.duplicateProperty"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit: string
{
	case Hearts = 'hearts';

	// Enums cannot have properties, but if they could,
	// this rule also applies to classes and interfaces
}
```

This error is reported by the same rule that checks classes and interfaces for duplicate property declarations:

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $prop;
	public int $prop;
}
```

## Why is it reported?

A property with the same name is declared more than once in the same class or enum. PHP does not allow redeclaring properties within a single class definition. This also applies when a property is declared both as a regular property and as a constructor-promoted property.

## How to fix it

Remove the duplicate property declaration:

```diff-php
 class Foo
 {
 	public int $prop;
-	public int $prop;
 }
```

If using constructor promotion, remove the separate property declaration:

```diff-php
 class Foo
 {
-	public int $prop;
-
 	public function __construct(
 		public int $prop,
 	) {}
 }
```
