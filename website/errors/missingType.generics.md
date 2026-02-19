---
title: "missingType.generics"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Collection
{
	/** @param T $item */
	public function add($item): void {}
}

class UserRepository
{
	/** @return Collection */ // ERROR: Class UserRepository has @return tag with generic class Collection but does not specify its types: T
	public function getUsers(): Collection
	{
		return new Collection();
	}
}
```

## Why is it reported?

A generic class is used without specifying its type parameters. Generic classes (those with `@template` tags) expect type arguments to be provided when they are referenced. Without type arguments, PHPStan cannot properly track the types flowing through the generic class, reducing the effectiveness of type checking.

This is similar to using `array` instead of `array<string, int>` -- it works, but PHPStan loses valuable type information.

## How to fix it

Specify the generic type parameters:

```diff-php
 <?php declare(strict_types = 1);

 class UserRepository
 {
-	/** @return Collection */
+	/** @return Collection<User> */
 	public function getUsers(): Collection
 	{
 		return new Collection();
 	}
 }
```

If the collection holds multiple types, specify all required type parameters as defined by the `@template` tags on the generic class:

```php
<?php declare(strict_types = 1);

/**
 * @template TKey
 * @template TValue
 */
class Map {}

/** @return Map<string, User> */
function getMap(): Map
{
	return new Map();
}
```

In PHPStan 1.x this error was possible to ignore with configuration parameter `checkGenericClassInNonGenericObjectType: false` but in PHPStan 2.0 this parameter was removed in favor of ignoring by identifier:
