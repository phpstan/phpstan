---
title: 'Solving PHPStan error "No value type specified in iterable type"'
date: 2021-02-02
tags: guides
---

This problem is reported by PHPStan on [level 6](/user-guide/rule-levels) when typehinting an array or an iterable object as a parameter type or a return type. It's there to prevent values of unknown types to be used as a foreach value:

```php
// Function foo() has parameter $items with no value type specified in iterable type array.
function foo(array $items): void
{
	foreach ($items as $item) {
		// type of $item is unknown here, static analysis can't find any bugs
	}
}
```

How to solve this error depends on what type has been used.

Arrays
======================

Solving this for arrays is straightforward - PHPStan needs to know what the array consists of. You can tell PHPStan the types of values and also keys in various ways in PHPDoc:

* `Type[]`
* `array<Type>`
* `array<int, Type>`
* `non-empty-array<Type>`
* `non-empty-array<int, Type>`

An example:

```php
/**
 * @param array<int, Item> $items
 */
function foo(array $items): void
{
	foreach ($items as $item) {
		// $item is Item
	}
}
```

If your codebase makes use of [arrays of various specific shapes](/writing-php-code/phpdoc-types#array-shapes) passed around functions and methods, PHPStan can check that the values in specified keys have the correct types. This is different from general arrays that mandate that all the keys and values must be of a specific homogeneous type. [Array shapes](/writing-php-code/phpdoc-types#array-shapes) allow each key and value to be different.

* `array{'foo': int, "bar": string}`
* `array{0: int, 1?: int}` (key `1` is optional in the array)
* `array{int, int}` (keys are `0` and `1`)
* `array{foo: int, bar: string}` (quotes around array keys aren't necessary)

Iterable objects
======================

The error is also reported for these cases:

* The typehint is a class that implements [`Iterator`](https://www.php.net/manual/en/class.iterator.php) interface
* The typehint is a class that implements [`IteratorAggregate`](https://www.php.net/manual/en/class.iteratoraggregate.php) interface
* The typehint is an interface that extends [`Traversable`](https://www.php.net/manual/en/class.traversable.php) interface

In case of `Iterator`, PHPStan looks at the return typehint of the `current()` method:

```php
/** @return Item */
public function current()
{
	// ...
}
```

In case of `IteratorAggregate`, PHPStan reads the return typehint of the `getIterator()` method:

```php
/** @return \ArrayIterator<int, Item> */
public function getIterator(): \Traversable
{
	// ...
}
```

The error can also be solved by using [generics](/blog/generics-in-php-using-phpdocs) with the help of `@extends` and `@implements` annotations. All three basic iterable types (`Iterator`, `IteratorAggregate`, `Traversable`) accept two type variables:

* `Iterator<TKey, TValue>`
* `IteratorAggregate<TKey, TValue>`
* `Traversable<TKey, TValue>`
 
You need to decide whether the iterated value will always going to be the same with the class, or if it's going to be different depending on the usage.  You might have a `DogCollection` that always iterates over instances of `Dog`, and you might have a `Collection` where you want the user to always decide what the `Collection` consists of.

In case of `DogCollection` the usage would look like this:

```php
/** @implements \IteratorAggregate<int, Dog> */
class DogCollection implements \IteratorAggregate
{

}
```

In case of general `Collection` it would look like this:

```php
/**
 * @template TKey
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue> 
 */
class Collection implements \IteratorAggregate
{

}
```

Or if the keys are always going to be the same:

```php
/**
 * @template TValue
 * @implements \IteratorAggregate<int, TValue> 
 */
class Collection implements \IteratorAggregate
{

}
```

Making the class generic with `@template` tags means that the user always needs to specify those types when typehinting the collection:

```php
/** @param Collection<Dog> */
function foo(Collection $items): void
{

}
```

Read more about these features in the [Generics guide](/blog/generics-in-php-using-phpdocs).

Third party code
======================

The above-mentioned ways of solving this problem can't be usually applied if the class you're typehinting comes from 3rd party code. Fortunately PHPStan comes with the [stub files](/user-guide/discovering-symbols) feature which is designed to override PHPDocs in 3rd party code.

Ignoring this error
======================

PHPStan understands that not everyone wants to dive into solving these errors when they first increase the [rule level](/user-guide/rule-levels) to level 6 so it offers a [config parameter](https://phpstan.org/config-reference#vague-typehints) to disable this check:

```yaml
parameters:
	checkMissingIterableValueType: false
```

But using [the baseline](/user-guide/baseline) to defer solving this error in an already existing codebase while preserving the check in newly written code is much more recommended.
