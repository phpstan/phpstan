---
title: PHPDoc Types
---

A PHPDoc type is what's written in place of `[Type]` in annotations like `@var [Type]` or `@param [Type] $foo`. [Learn more about PHPDoc basics »](/writing-php-code/phpdocs-basics)

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
	
Would you like to use advanced PHPDoc types in 3<sup>rd</sup> party code you have in /vendor? You can! Check out [Stub Files »](/user-guide/stub-files)

</div>

Basic types
-------------------------

* `int`, `integer`
* `string`
* `array-key`
* `bool`, `boolean`
* `true`
* `false`
* `null`
* `float`
* `double`
* `scalar`
* `array`
* `iterable`
* `callable`, `pure-callable`
* `resource`, `closed-resource`, `open-resource`
* `void`
* `object`

Mixed
-------------------------

`mixed` type can be used if we don't want to define a more specific type. PHPStan doesn't check anything on the `mixed` type - any property or method can be called on it and it can be passed to any type in a function/method call.

PHPStan has a concept of implicit and explicit `mixed`. Missing typehint is implicit `mixed` - no type was specified as a parameter type or a return type. Explicit `mixed` is written in the PHPDoc. PHPStan's [rule level 6](/user-guide/rule-levels) isn't satisfied with implicit `mixed`, but an explicit one is sufficient.

[Rule level 9](/user-guide/rule-levels) is stricter about the `mixed` type. The only allowed operation you can do with it is to pass it to another `mixed`.

Classes and interfaces
-------------------------

A fully-qualified name (FQN) like `\Foo\Bar\Baz`, or a relative name like `Baz` resolved based on the current namespace and `use` statements can be used.

Trait names cannot be used in PHPDocs, as they [don't work as native PHP typehints](https://3v4l.org/Ifr2J) either.

Integer ranges
-----------------------

* `positive-int`
* `negative-int`
* `non-positive-int`
* `non-negative-int`
* `non-zero-int`
* `int<0, 100>`
* `int<min, 100>`
* `int<50, max>`

General arrays
-------------------------

* `Type[]`
* `array<Type>`
* `array<int, Type>`
* `non-empty-array<Type>`
* `non-empty-array<int, Type>`

Lists
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

* `list<Type>`
* `non-empty-list<Type>`

Lists are arrays with sequential integer keys starting at 0.

Key and value types of arrays and iterables
-------------------------

* `key-of<Type::ARRAY_CONST>`
* `value-of<Type::ARRAY_CONST>`

```php
class Foo {
   public const WHEELER = [
      'car' => 4,
      'bike' => 2,
   ];
}

/**
 * @param key-of<Foo::WHEELER> $type
 * @param value-of<Foo::WHEELER> $wheels
 */
function repair(string $type, int $wheels): void
{
    // $type is 'bike'|'car'
    // $wheels is 2|4
}
```

Additionally `value-of<BackedEnum>` is supported:

```php
enum Suit: string
{
    case Hearts = 'H';
    case Spades = 'S';
}

/**
 * @param array<value-of<Suit>, int> $count
 */
function foo(array $count): void
{
    // Example for $count: ['H' => 2, 'S' => 3]
}
```

Iterables
-------------------------

* `iterable<Type>`
* `Collection<Type>`
* `Collection<int, Type>`
* `Collection|Type[]`

These notations specify the iterable key and value types in a foreach statement.

These iterable rules are applied only when the `Collection` type isn't generic. When it's generic, [generics rules](/blog/generics-in-php-using-phpdocs) for class-level type variables are applied.

If PHPStan encounters `Collection|Foo[]`, two possible paths are taken:

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

[Generics »](/blog/generics-in-php-using-phpdocs), [Generics By Examples »](/blog/generics-by-examples)

Conditional return types
-------------------------

A simpler alternative to generics if you just want to infer the return type based on if-else logic.


```php
/**
 * @return ($size is positive-int ? non-empty-array : array)
 */
function fillArray(int $size): array
{
	...
}
```

It can be combined with generics as well in both the condition and the if-else types:

```php
/**
 * @template T of int|array<int>
 * @param T $id
 * @return (T is int ? static : array<static>)
 */
public function fetch(int|array $id)
{
	...
}
```

Utility types for generics
-------------------------

`template-type` can be used to [get `@template` type from a passed object argument](https://phpstan.org/r/ceb59974-0a7c-492a-867a-5d5b7c30e52f). Related discussion [here](https://github.com/phpstan/phpstan/discussions/9053).

`new` can be used to [create an object type from a class-string type](https://phpstan.org/r/a01e1e49-6f05-43a8-aac7-aded770cd88a).


class-string
-------------------------

`class-string` type can be used wherever a valid class name string is expected. [Generic](/blog/generics-in-php-using-phpdocs) variant `class-string<T>` also works, or you can use `class-string<Foo>` to only accept valid class names that are subtypes of Foo.

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

`callable-string` is a string that PHP considers a valid [`callable`](https://www.php.net/manual/en/language.types.callable.php).

`numeric-string` is a string that would pass an [`is_numeric()`](https://www.php.net/manual/en/function.is-numeric.php) check.

`non-empty-string` is any string except `''`. It does _not_ mean "empty" in the weird sense used by [`empty()`](https://www.php.net/manual/en/function.empty.php).

`non-falsy-string` (also known as `truthy-string`) is any string that is true after casting to boolean.

Security-focused `literal-string` is inspired by the [`is_literal()` RFC](https://wiki.php.net/rfc/is_literal). In short, it means a string that is either written by a developer or composed only of developer-written strings.

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

To use a local type alias elsewhere, you can import it using the `@phpstan-import-type` annotation in another class' PHPDocs:

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
* `array{'foo': int, "bar"?: string}` (key `bar` is optional in the array)
* `array{int, int}` (keys are `0` and `1`, also known as a tuple)
* `array{0: int, 1?: int}` (key `1` is optional in the array)
* `array{foo: int, bar: string}` (quotes around array keys for simple strings aren't necessary)

This is different from [general arrays](#general-arrays) that mandate that all the keys and values must be of a specific homogeneous type. Array shapes allow each key and value to be different.

Object shapes
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.12</div>

This feature is inspired by array shapes but represents objects with public properties with specified types:

* `object{'foo': int, "bar": string}`
* `object{'foo': int, "bar"?: string}` (property `bar` is optional in the object)
* `object{foo: int, bar?: string}` (quotes around property names aren't necessary)

Object shape properties are read-only. You can intersect the object shape with another class to make them writable:

* `object{foo: int, bar?: string}&\stdClass`

Literals and constants
-------------------------

PHPStan allows specifying scalar values as types in PHPDocs:

* `234` (integers)
* `1.0` (floats)
* `'foo'|'bar'` (strings; types can be combined with others)

Constant enumerations are also supported:

* `Foo::SOME_CONSTANT`
* `Foo::SOME_CONSTANT|Bar::OTHER_CONSTANT`
* `self::SOME_*` (all constants on `self` that start with `SOME_`)
* `Foo::*` (all constants on `Foo`)

Global constants
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.6</div>

Constants are supported as long as they don't contain lowercase letters and a class with the same name doesn't exist:

* `SOME_CONSTANT`
* `SOME_CONSTANT|OTHER_CONSTANT`

Callables
-------------------------

The `callable` typehint has been in PHP for a long time. But it doesn't allow enforcing specific callback signatures. However, PHPStan allows and enforce specific signatures in PHPDocs:

* `callable(int, int): string` (accepts two integers, returns a string)
* `callable(int, int=): string` (second parameter is optional)
* `callable(int $foo, string $bar): void` (accepts an integer and a string, doesn't return anything; parameter names are optional and insignificant)
* `callable(string &$bar): mixed` (accepts a string parameter passed by reference, returns `mixed`)
* `callable(float ...$floats): (int|null)` (accepts multiple variadic float arguments, returns integer or null)
* `callable(float...): (int|null)` (accepts multiple variadic float arguments, returns integer or null)
* `\Closure(int, int): string` (narrower `Closure` type can also be used instead of `callable`)
* `pure-callable(int, int): string` (callable that doesn't have any side effects when called)
* `pure-Closure(int, int): string` (Closure that doesn't have any side effects when called)

Parameter types and return type are required. Use `mixed` if you don't want to use a more specific type.

Aside from describing callable signatures PHPStan also supports declaring whether the callable is [executed immediately or saved for later](/writing-php-code/phpdocs-basics#callables) when passed into a function or a method.

PHPStan also supports [changing the meaning of `$this`](/writing-php-code/phpdocs-basics#callables) inside a closure with `@param-closure-this` PHPDoc tag.

Bottom type
-------------------------

All of these names are equivalent:

* `never`
* `never-return`
* `never-returns`
* `no-return`

Marking a function or a method as `@return never` tells PHPStan the function always throws an exception, or contains a way to end the script execution, like `die()` or `exit()`. This is useful when [solving undefined variables](/writing-php-code/solving-undefined-variables).

Integer masks
-------------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.6.0</div>

Some functions accept a bitmask composed by `|`-ing different integer values. `0` is always part of the possible values. Some examples:

* `int-mask<1, 2, 4>` (accepts values that can be composed using `|` from the given integers, and 0)
* `int-mask-of<1|2|4>` (the same as above, but written as a union)
* `int-mask-of<Foo::INT_*>` (accepts values from all constants on `Foo` that start with `INT_`)

Offset access
-------------------------

You can access a value type of a specific array key:

```php
/**
 * @phpstan-type MyArray array{foo: int, bar: string}
 */
class HelloWorld
{

	/** @return MyArray['bar'] */
	public function getBar()
	{
		// this needs to return a string...
	}
}
```

This feature shines when combined with generics. It allows to represent key-value pairs backed by an array:

```php
/**
 * @template T of array<string, mixed>
 */
trait AttributeTrait
{
	/** @var T */
	private array $attributes;

	/**
	 * @template K of key-of<T>
	 * @param K $key
	 * @param T[K] $val
	 */
	public function setAttribute(string $key, $val): void
	{
		// ...
	}

	/**
	 * @template K of key-of<T>
	 * @param K $key
	 * @return T[K]|null
	 */
	public function getAttribute(string $key)
	{
		return $this->attributes[$key] ?? null;
	}
}

class Foo {

	/** @use AttributeTrait<array{foo?: string, bar?: 5|6|7, baz?: bool}> */
	use AttributeTrait;

}
```

When we try to use class `Foo` in practice, PHPStan reports expected type errors:

```php
$f = new Foo;
$f->setAttribute('bar', 5); // OK, bar can be 5
$f->setAttribute('foo', 3); // error, foo cannot be 3
$f->getAttribute('unknown'); // error, unknown key
```
