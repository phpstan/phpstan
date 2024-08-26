---
title: "Generics By Examples"
date: 2022-03-14
tags: guides
---

There's a comprehensive guide on [generics in PHP using PHPDocs](/blog/generics-in-php-using-phpdocs), but after reading it someone might still not be sure where to begin.

That's why I collected these examples of generics in practice. It's here to give you an idea how to actually use generics in real-world code every day.

Return the same type the function accepts
------------------------

```php
/**
 * @template T
 * @param T $p
 * @return T
 */
function foo($p)
{
    // ...
}
```

Return the same type the function accepts and limit what the type can be
------------------------

The `of Foo` part is called "template type variable bound".

```php
/**
 * @template T of Foo
 * @param T $p
 * @return T
 */
function foo(Foo $p): Foo
{
    // ...
}
```

Accept a class string and return the object of that type
------------------------

```php
/**
 * @template T of object
 * @param class-string<T> $className
 * @return T
 */
function foo(string $className): object
{
    // ...
}
```

Accept a class string and return an array of objects of that type
------------------------

```php
/**
 * @template T of object
 * @param class-string<T> $className
 * @return array<int, T>
 */
function foo(string $className): array
{
    // ...
}
```

Accept an array and return an array with the same key type
------------------------

```php
/**
 * @template TKey of array-key
 * @param array<TKey, mixed> $p
 * @return array<TKey, mixed>
 */
function foo(array $p): array
{
    // ...
}
```

Define a generic interface
------------------------

```php
/**
 * @template T
 */
interface Collection
{
    /** @param T $item */
    public function add($item): void;

    /** @return T */
    public function get();
}
```

Specify template type variable of a generic interface or class in a parameter type
------------------------

Specifying the template type with `Collection<Foo>` will get you rid of this error:

> Function foo() has parameter $c with generic interface Collection but does not specify its types: T

```php
/**
 * @param Collection<Foo> $c
 */
function foo(Collection $c): void
{
    // ...
}
```

Specify template type variable of a generic interface when implementing it
------------------------

Specifying the template type with `@implements Collection<Foo>` will get you rid of this error:

> Class Bar implements generic interface Collection but does not specify its types: T

```php
/** @implements Collection<Foo> */
class Bar implements Collection
{
}
```

If you want the child class to be also generic, repeat `@template` above it:

```php
/**
 * @template T
 * @implements Collection<T>
 */
class Bar implements Collection
{
}
```

The child class can also have different number and names of `@template` tags.

Specify template type variable of a generic class when extending it
------------------------

The `@extends` tag needs to be used instead of `@implements`:

```php
/** @extends Collection<Foo> */
class Bar extends Collection
{
}
```

This will get you rid of the following error:

> Class Bar extends generic class Collection but does not specify its types: T

Function accepts any string, but returns object of the same type if it's a class-string
------------------------

Typical scenario for the service locator (dependency injection container) pattern.

The following code doesn't work because `string|class-string<T>` is [normalized](/developing-extensions/type-system#type-normalization) to `string`:

```php
class Container
{
	/**
	 * @template T of object
	 * @param string|class-string<T> $name
	 * @return ($name is class-string ? T : object)
	 */
	public function get(string $name): object
	{
		// ...
	}
}
```

But the following code works as expected:

```php
class Container
{
	/**
	 * @template T of object
	 * @return ($name is class-string<T> ? T : object)
	 */
	public function get(string $name): object
	{
		// ...
	}
}
```

If your container only holds services that are named with their FQCN you could write it like:

```php
class Container
{
	/**
	 * @template T of object
	 * @param class-string<T> $name
	 * @return T
	 */
	public function get(string $name): object
	{
		// ...
	}
}
```

Container with static services
----------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11</div>

If you have a container with a static array of services, you can use `new` to return the correct type like this:

```php
class Container
{
	/**
	 * @var array<string, class-string>
	 */
	private const TYPES = [
		'foo' => DateTime::class,
		'bar' => DateTimeImmutable::class,
	];

	/**
	 * @template T of key-of<self::TYPES>
	 * @param T $type
	 *
	 * @return new<self::TYPES[T]>
	 */
	public static function get(string $type) : object
	{
		$class = self::TYPES[$type] ?? throw new InvalidArgumentException('Not found');

		return new $class();
	}
}
```

Couple relevant classes together
------------------------

The following example is especially useful when you want to couple together things like an Event + its EventHandler.

Let's say you have the following interfaces and classes:

```php
interface Original
{
}

interface Derivative
{
}

class MyOriginal implements Original
{
}

class MyDerivative implements Derivative
{
}
```

And you have a function that returns `MyDerivative` when `MyOriginal` is given:

```php
function foo(Original $o): Derivative
{
    // ...
}
```

PHPStan out of the box unfortunately can't know that:

```php
$d = foo(new MyOriginal());
\PHPStan\dumpType($d); // Derivative, not MyDerivative :(
```

But you can couple those relevant classes together with the help of generics. First, you have to make Original generic so that it's aware of its derivative:

```php
/** @template TDerivative of Derivative */
interface Original
{
}
```

After that you need to say that `MyOriginal` is coupled to `MyDerivative`:

```php
/** @implements Original<MyDerivative> */
class MyOriginal implements Original
{
}
```

And you need to modify the signature of the `foo()` function to "extract" the `Derivative` from the given `Original`:

```php
/**
 * @template T of Derivative
 * @param Original<T> $o
 * @return T
 */
function foo(Original $o): Derivative
{
    // ...
}
```

And once you do all that, the type in the following code is correctly inferred:

```php
$d = foo(new MyOriginal());
\PHPStan\dumpType($d); // MyDerivative :)
```

[Link to this example on the playground »](https://phpstan.org/r/a2f3ac06-8daf-4d59-a5da-4ef4f0a1ebe8)

Specify template type variable of a generic trait when using it
------------------------

Traits can be generic too:

```php
/** @template T of Foo */
trait Part
{
    /** @param T $item */
    public function bar(Foo $item): void
    {
        // ...
    }
}
```

Into a class using a generic trait, the `@use` tag can be defined on the PHPDoc of the use clause:

```php
/** @template T of Foo */
class Bar
{
    /** @use Part<T> */
    use Part;
}
```

The `@use` tag can be defined into the PHPDoc above the `class` or the `use` clause. Both work the same way:

```php
/**
 * @template T of Foo
 * @use Part<T> 
 */
class Bar
{
    use Part;
}
```
