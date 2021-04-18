---
title: PHPDoc Types
---

A PHPDoc type is what's written in place of `[Type]` in annotations like `@var [Type]` or `@param [Type] $foo`.

Basic types
-------------------------

* `int`, `integer`
* `positive-int`, `negative-int`
* `string`
* `bool`, `boolean`
* `true`
* `false`
* `null`
* `float`
* `double`
* `array`
* `iterable`
* `callable`
* `resource`
* `void`
* `object`

Mixed
-------------------------

`mixed` type can be used if we don't want to define a more specific type. PHPStan doesn't check anything on the `mixed` type - any property or method can be called on it and it can be passed to any type in a function/method call.

PHPStan has a concept of implicit and explicit `mixed`. Missing typehint is implicit `mixed` - no type was specified as a parameter type or a return type. Explicit `mixed` is written in the PHPDoc. PHPStan's [rule level 6](/user-guide/rule-levels) isn't satisfied with implicit `mixed`, but an explicit one is sufficient.

Classes and interfaces
-------------------------

A fully-qualified name (FQN) like `\Foo\Bar\Baz`, or a relative name like `Baz` resolved based on the current namespace and `use` statements can be used.

Trait names cannot be used in PHPDocs, as they [don't work as native PHP typehints](https://3v4l.org/Ifr2J) either.

General arrays
-------------------------

* `Type[]`
* `array<Type>`
* `array<int, Type>`
* `non-empty-array<Type>`
* `non-empty-array<int, Type>`

Iterables
-------------------------

* `iterable<Type>`
* `Collection<Type>`
* `Collection<int, Type>`
* `Collection|Type[]`

These notations specify the iterable key and value types in a foreach statement.

These iterable rules are applied only when the `Collection` type isn't generic. When it's generic, [generics rules](/blog/generics-in-php-using-phpdocs) for class-level type variables are applied.

If PHP encounters `Collection|Foo[]`, two possible paths are taken:

1) `Collection` implements `Traversable` so `Collection|Foo[]` is interpreted as a `Collection` object that iterates over `Foo`. The array part isn't applied.
2) `Collection` does not implement `Traversable` so `Collection|Foo[]` is interpreted as a `Collection` object or an array of `Foo` objects.

If `Collection|Foo[]` means "`Collection` or array" in your case even if `Collection` implements `Traversable`, you need to disambiguate the type by using `Collection|array<Foo>` instead.

Union types
-------------------------

Written as `Type1|Type2`. [Read more about union types here »](/blog/union-types-vs-intersection-types)

Intersection types
-------------------------

Written as `Type1&Type2`. [Read more about intersection types here »](/blog/union-types-vs-intersection-types)

Parentheses
-------------------------

Parentheses can be used to disambiguate types: `(Type1&Type2)|Type3`

static and $this
-------------------------

To denote that a method returns the same type it's called on, use `@return static` or `@return $this`.

This is useful if we want to tell that a method from a parent class will return an object of the child class when the parent class is extended ([see example](/r/5f856517-5303-4237-95de-2bfa5bc4b9de)).

A narrower `@return $this` instead of `@return static` can also be used, and PHPStan will check if you're really returning the same object instance and not just an object of the child class.

Generics
-------------------------

[Generics »](/blog/generics-in-php-using-phpdocs)

class-string
-------------------------

`class-string` type can be used wherever a valid class name string is expected. [Generic](/blog/generics-in-php-using-phpdocs) variant `class-string<T>` also works.

```php
/**
 * @param class-string $className
 */
function foo(string $className): void { ... }
```

Both literal strings with valid class names (`'stdClass'`) and `class` constants (`\stdClass::class`) are accepted as `class-string` arguments.

If you have a general `string` and want to pass it as a `class-string` argument, you need to make sure the string contains a valid class name:

```php
function bar(string $name): void
{
    if (class_exists($name)) { // or interface_exists()
        // $name is class-string here
        foo($name);
    }
}
```

Other advanced string types
-------------------------

There's also `callable-string` and `numeric-string`.

Global type aliases
-------------------------

Type aliases (also known as `typedef`) are a popular feature in other languages like TypeScript or C++. Defining type aliases will allow you to reference complex types in your PHPDocs by their alias.

You can define global type aliases in the [configuration file](/config-reference):

```neon
parameters:
	typeAliases:
		Name: 'string'
		NameResolver: 'callable(): string'
		NameOrResolver: 'Name|NameResolver'
```

Then you can use these aliases in your codebase:

```php
/**
 * @param NameOrResolver $arg
 */
function foo($arg)
{
	// $arg is string|(callable(): string)
}
```

Local type aliases
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 0.12.84</div>

You can also define and use local aliases in PHPDocs using the `@phpstan-type` annotation. These are scoped to the class that defines them:

```php
/**
 * @phpstan-type UserAddress array{street: string, city: string, zip: string}
 */
class User
{
	/**
	 * @var UserAddress
	 */
	private $address; // is of type array{street: string, city: string, zip: string}
}
```

To use a local type alias elsewhere, you can import it using the `@phpstan-import-type` annotation:

```php
/**
 * @phpstan-import-type UserAddress from User
 */
class Order
{
	/** @var UserAddress */
	private $deliveryAddress; // is of type array{street: string, city: string, zip: string}
}
```

You can optionally change the name of the imported alias:

```php
/**
 * @phpstan-import-type UserAddress from User as DeliveryAddress
 */
class Order
{
	/** @var DeliveryAddress */
	private $deliveryAddress; // is of type array{street: string, city: string, zip: string}
}
```

Array shapes
-------------------------

This feature enables usage of strong types in codebases where arrays of various specific shapes are passed around functions and methods. PHPStan checks that the values in specified keys have the correct types:

* `array{'foo': int, "bar": string}`
* `array{0: int, 1?: int}` (key `1` is optional in the array)
* `array{int, int}` (keys are `0` and `1`)
* `array{foo: int, bar: string}` (quotes around array keys aren't necessary)

This is different from [general arrays](#general-arrays) that mandate that all the keys and values must be of a specific homogeneous type. Array shapes allow each key and value to be different.

Literals and constants
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 0.12.20</div>

PHPStan allows specifying scalar values as types in PHPDocs:

* `234` (integers)
* `1.0` (floats)
* `'foo'|'bar'` (strings; types can be combined with others)

Constant enumerations are also supported:

* `Foo::SOME_CONSTANT`
* `Foo::SOME_CONSTANT|Bar::OTHER_CONSTANT`
* `self::SOME_*` (all constants on `self` that start with `SOME_`)
* `Foo::*` (all constants on `Foo`)

Callables
-------------------------

The `callable` typehint has been in PHP for a long time. But it doesn't allow enforcing specific callback signatures. However, PHPStan allows and enforce specific signatures in PHPDocs:

* `callable(int, int): string` (accepts two integers, returns a string)
* `callable(int, int=): string` (second parameter is optional)
* `callable(int $foo, string $bar): void` (accepts an integer and a string, doesn't return anything; parameter names are optional and insignificant)
* `callable(string &$bar): mixed` (accepts a string parameter passed by reference, returns `mixed`)
* `callable(float ...$floats): (int|null)` (accepts multiple variadic float arguments, returns integer or null)

Parameter types and return type are required. Use `mixed` if you don't want to use a more specific type.

Bottom type
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 0.12.54</div>

All of these names are equivalent:

* `never`
* `never-return`
* `never-returns`
* `no-return`

Marking a function or a method as `@return never` tells PHPStan the function always throws an exception, or contains a way to end the script execution, like `die()` or `exit()`. This is useful when [solving undefined variables](/writing-php-code/solving-undefined-variables).
