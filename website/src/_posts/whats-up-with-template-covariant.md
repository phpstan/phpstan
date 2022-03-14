---
title: "What's Up With @template-covariant?"
date: 2022-03-14
tags: guides
---

Let's say you have a hierarchy like this:

```php
interface Animal {}
class Dog implements Animal {}
class Cat implements Animal {}
```

When thinking about [generics in PHP using PHPDocs](/blog/generics-in-php-using-phpdocs), it's reasonable to expect that `Collection<Cat>` can be passed into a parameter where `Collection<Animal>` is expected, since `Cat` is an `Animal`. It might surprise you when PHPStan reports this error:

> Parameter #1 $animals of function foo expects `Collection<Animal>`, `Collection<Cat>` given.

This is because `@template` is invariant, meaning that `Collection<Animal>` only accepts another `Collection<Animal>`.

Consider this code:

```php
/** @param Collection<Animal> $animals */
function foo(Collection $animals): void
{
	$animals->add(new Dog()); // valid, no harm done
}
```

Because the code is harmless and no error is reported there, we can't pass `Collection<Cat>` there, because it'd no longer be a collection of cats, there'd be a dog among them after this call.

So if we want to pass `Collection<Cat>` into `Collection<Animal>`, the template type in question has to use `@template-covariant` PHPDoc tag. But it comes with a different limitation.

Since we can now pass `Collection<Cat>` into `Collection<Animal>`, this code is no longer valid:

```php
/** @param Collection<Animal> $animals */
function foo(Collection $animals): void
{
	$animals->add(new Dog());
}
```

The `@template-covariant X` tag doesn't actually allow you to use `X` in a parameter position, so with code like this:

```php
/**
 * @template-covariant TItem
 */
class Collection
{

	/** @var TItem[] */
	private array $items = [];

	/** @param TItem $item */
	public function add($item): void
	{
		// you can pass Collection<Cat> to Collection<Animal>, but you can't have "TItem" in parameter position
		$this->items[] = $item;
	}

	/** @return TItem|null */
	public function get(int $index)
	{
		return $this->items[$index] ?? null;
	}

}
```

You'll get this error:

> Template type TItem is declared as covariant, but occurs in contravariant position in parameter item of method `Collection::add()`.

To summarize:

* `@template` declares an invariant type variable: The object `Collection<Animal>` accepts only another `Collection<Animal>`. But the collection can be mutable and the type variable can be present in a parameter position. [Playground example »](https://phpstan.org/r/81513715-c26f-4a25-9709-a956d6d3e02b)
* `@template-covariant` declares a covariant type variable: The object `Collection<Animal>` also accepts `Collection<Cat>`, but the type variable cannot be present in a parameter position. [Playground example »](https://phpstan.org/r/d2f62e2c-52fc-4956-87ea-fc4c8d481384)
