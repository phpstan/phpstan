---
title: "missingType.generics"
shortDescription: "Generic class is used without specifying its type parameters."
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
	/** @return Collection */ // Method UserRepository::getUsers() return type with generic class Collection does not specify its types: T
	public function getUsers(): Collection
	{
		return new Collection();
	}
}
```

## Why is it reported?

A [generic class](/blog/generics-in-php-using-phpdocs) is used without specifying its type parameters. Generic classes (those with `@template` tags) expect type arguments to be provided when referenced. Without type arguments, PHPStan cannot properly track the types flowing through the generic class, reducing the effectiveness of type checking.

This is similar to using `array` instead of `array<string, int>` — it works, but PHPStan loses valuable type information.

This error is reported in many different situations — anywhere a generic class or interface can appear without its type parameters specified.

### Class ancestors

When a class extends a generic class, implements a generic interface, or uses a generic trait without specifying type parameters:

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Collection {}

/**
 * @template T
 */
interface Comparable {}

/**
 * @template T of object
 */
trait Identifiable {}

class UserCollection extends Collection // Class UserCollection extends generic class Collection but does not specify its types: T
	implements Comparable // Class UserCollection implements generic interface Comparable but does not specify its types: T
{
	use Identifiable; // Class UserCollection uses generic trait Identifiable but does not specify its types: T
}
```

The same applies to interfaces extending generic interfaces and enums implementing generic interfaces:

```php
<?php declare(strict_types = 1);

interface UserComparable extends Comparable // Interface UserComparable extends generic interface Comparable but does not specify its types: T
{
}

enum Status implements Comparable // Enum Status implements generic interface Comparable but does not specify its types: T
{
}
```

### Properties, parameters, and return types

The error is reported on properties, method/function parameters, and return types that reference a generic class without type parameters:

```php
<?php declare(strict_types = 1);

class UserService
{
	/** @var Collection */
	private $users; // Property UserService::$users with generic class Collection does not specify its types: T

	/** @param Collection $items */
	public function process($items): void {} // Method UserService::process() has parameter $items with generic class Collection but does not specify its types: T

	/** @return Collection */
	public function getAll() {} // Method UserService::getAll() return type with generic class Collection does not specify its types: T
}
```

This also applies to global functions:

```php
<?php declare(strict_types = 1);

/** @param Collection $items */
function process($items): void {} // Function process() has parameter $items with generic class Collection but does not specify its types: T
```

### PHPDoc tags

The error is reported for various PHPDoc tags that reference generic classes without type parameters:

- `@var` tags on statements
- `@property`, `@property-read`, `@property-write` tags
- `@method` tags
- `@mixin` tags
- `@phpstan-self-out` tags
- `@phpstan-assert`, `@phpstan-assert-if-true`, `@phpstan-assert-if-false` tags
- `@phpstan-type` (local type aliases)

### Class constants and property hooks

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var Collection */
	const ITEMS = null; // Constant Foo::ITEMS with generic class Collection does not specify its types: T
}
```

## How to fix it

Specify the generic type parameters using PHPDoc. The approach differs depending on where the generic class appears.

### For class ancestors

Use `@extends` when extending a generic class, `@implements` when implementing a generic interface, and `@use` when using a generic trait. See [PHPDoc Basics](/writing-php-code/phpdocs-basics) for more details.

```diff-php
+/**
+ * @extends Collection<User>
+ * @implements Comparable<User>
+ */
 class UserCollection extends Collection
 	implements Comparable
 {
+	/** @use Identifiable<User> */
 	use Identifiable;
 }
```

For interfaces extending generic interfaces:

```diff-php
+/** @extends Comparable<User> */
 interface UserComparable extends Comparable
 {
 }
```

For enums implementing generic interfaces:

```diff-php
+/** @implements Comparable<string> */
 enum Status implements Comparable
 {
 }
```

### For properties, parameters, and return types

Specify the type parameters in the corresponding PHPDoc tag:

```diff-php
 class UserService
 {
-	/** @var Collection */
+	/** @var Collection<User> */
 	private $users;

-	/** @param Collection $items */
+	/** @param Collection<User> $items */
 	public function process($items): void {}

-	/** @return Collection */
+	/** @return Collection<User> */
 	public function getAll() {}
 }
```

### Multiple type parameters

If the generic class has multiple `@template` tags, specify all required type parameters:

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

Some template types may have defaults (`@template T = mixed`). In that case only the required parameters need to be specified, but specifying all of them gives PHPStan the most type information.

In PHPStan 1.x this error was possible to ignore with configuration parameter `checkGenericClassInNonGenericObjectType: false` but in PHPStan 2.0 this parameter was removed in favor of ignoring by identifier. Learn more in the [upgrading guide](/blog/upgrading-from-phpstan-1-to-2#removed-option-checkgenericclassinnongenericobjecttype).
