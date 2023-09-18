---
title: "A guide to call-site generic variance"
date: 2023-09-18
tags: guides
---
PHPStan has supported what is called declaration-site variance for a long time. An example you might be familiar with is the infamous [`@template-covariant`](/blog/whats-up-with-template-covariant). And although not as often useful, it also has [a contravariant counterpart](https://jiripudil.cz/blog/contravariant-template-types).

To freshen your memory: you can mark a template type as covariant:

```php
/** @template-covariant ItemType */
interface Collection
{
	/** @param ItemType $item */
	public function add(mixed $item): void;

	/** @return ItemType|null */
	public function get(int $index): mixed;
}
```

This allows you to pass `Collection<Cat>` into functions where `Collection<Animal>` is expected. But it comes at a cost:

```php
/** @param Collection<Animal> $animals */
function foo(Collection $animals): void
{
	$animals->add(new Dog());
}
```

By itself, this is a perfectly valid code. But with `Collection` being covariant over its template type, we can now mix cats with dogs. That's why PHPStan prevents us from using the `ItemType` in `Collection::add()` method's parameter:

> Template type ItemType is declared as covariant, but occurs in contravariant position in parameter item of method `Collection::add()`.

That's the trade-off: if you want the `Collection` to be covariant, it can only have the `get` method. If you want it to have the `add` method too, it has to be invariant in its `ItemType`.

Until now, there has not been an easy way around this. You could split the interface into a read-only one that can safely be covariant, and an invariant one:

```php
/** @template-covariant ItemType */
interface ReadonlyCollection
{
	/** @return ItemType|null */
	public function get(int $index): mixed;
}

/**
 * @template ItemType
 * @extends ReadonlyCollection<ItemType>
 */
interface Collection extends ReadonlyCollection
{
	/** @param ItemType $item */
	public function add(mixed $item): void;
}
```

But that is a lot of work and can quickly get tedious. Call-site variance is a way of having PHPStan do this work for you.


## Call-site variance

As the name suggests, call-site variance (or type projections, if you prefer fancier words) moves the variance annotation from the declaration to the call site. This means that the declaration of the interface can remain invariant, and therefore contain both `get` and `add` methods:

```php
/** @template ItemType */
interface Collection
{
	/** @param ItemType $item */
	public function add(mixed $item): void;

	/** @return ItemType|null */
	public function get(int $index): mixed;
}
```

If you need a specific function to accept `Collection<Cat>` in place of `Collection<Animal>`, you can instruct it so by attaching the `covariant` keyword to the generic type argument:

```php
/** @param Collection<covariant Animal> $animals */
function foo(Collection $animals): void
{
	$animals->add(new Dog());
}
```

Correspondingly, the error has moved from the declaration to the call-site: if the implementation does something that would break type safety, like adding a `Dog` into the collection above, PHPStan will tell us:

> Parameter #1 $item of method Collection<covariant Animal>::add() expects never, Dog given.


## Call-site contravariance

Although not as useful, it's worth mentioning that you can also use contravariant type projections. For example, if we wanted a happy little function that fills a collection with dogs, we could make it so that it accepts not only `Collection<Dog>`, but also `Collection<Animal>`, or even `Collection<mixed>`:

```php
/** @param Collection<contravariant Dog> $collection */
function fill(Collection $collection)
{
	while (true) {
		$collection->add(new Dog);
	}
}
```

This too has a limitation, but at least this time it's not so drastic: if we wanted to `get` a value from the collection, we can:

```php
/** @param Collection<contravariant Dog> $collection */
function fill(Collection $collection)
{
	while ($collection->get(42) === null) {
		$collection->add(new Dog);
	}
}
```

But because a contravariant type is not bounded from the top, we cannot make any assumption about the type of the retrieved item, and neither can PHPStan, therefore the type of `$collection->get(42)` would be `mixed`.


## Star projections

Sometimes, you just don't care about the type of the values you're working with. Let's add a `count()` method to the `Collection`:

```php
/** @template ItemType */
interface Collection
{
	/** @param ItemType $item */
	public function add(mixed $item): void;

	/** @return ItemType|null */
	public function get(int $index): mixed;

	public function count(): int;
}
```

This method doesn't reference the `ItemType` template type at all. We are using it in a function `printSize` that prints the size of a collection. In this case, the function can accept a collection of _anything_. Inspired by other languages such as Kotlin, PHPStan provides an idiomatic way of writing this, using an asterisk:

```php
/** @param Collection<*> $collection */
function printSize(Collection $collection): int
{
	echo $collection->count();
}
```

Obviously, we cannot make any assumptions about the collection's item type _whatsoever_. In other words, star projections combine the limitations of covariant and contravariant projections. If we were to `add()` anything into the collection inside this `printSize` function, we would get a similar error as above:

> Parameter #1 $item of method Collection<*>::add() expects never, Dog given.

And if we wanted to `get` a value from the collection, it would be `mixed`:

```php
/** @param Collection<*> $collection */
function printSize(Collection $collection): int
{
	$item = $collection->get(0);
	// $item is mixed
}
```
