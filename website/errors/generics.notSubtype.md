---
title: "generics.notSubtype"
shortDescription: "Type argument does not satisfy the template type bound constraint."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @template T of \Countable */
class Collection
{
	/** @var list<T> */
	private array $items = [];
}

/** @var Collection<\stdClass> $c */
$c = new Collection();
```

## Why is it reported?

The type argument `\stdClass` provided to the generic type `Collection` is not a subtype of the template type's bound `\Countable`. The class declares `@template T of \Countable`, which means any type argument must implement `\Countable`. Since `\stdClass` does not implement `\Countable`, it violates this constraint.

This ensures that generic types are used with compatible type arguments that satisfy the declared constraints.

## How to fix it

Use a type that satisfies the template bound:

```diff-php
-/** @var Collection<\stdClass> $c */
+/** @var Collection<\ArrayIterator<int, string>> $c */
 $c = new Collection();
```

Or if the bound is too restrictive, loosen it:

```diff-php
-/** @template T of \Countable */
+/** @template T of object */
 class Collection
 {
 	/** @var list<T> */
 	private array $items = [];
 }
```
