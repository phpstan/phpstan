---
title: "generics.interfaceConflict"
shortDescription: "Same generic interface is inherited with conflicting type arguments."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Item {}

/**
 * @extends \Traversable<int, Item>
 */
interface ItemListInterface extends \Traversable
{
}

/**
 * @implements \IteratorAggregate<int, string>
 */
final class ItemList implements \IteratorAggregate, ItemListInterface
{
	public function getIterator(): \Traversable
	{
		return new \ArrayIterator([]);
	}
}
```

## Why is it reported?

A class or interface specifies conflicting type arguments for the same generic interface through different inheritance paths. When a class implements or extends the same generic interface multiple times (through different parent interfaces or classes), the template type arguments must be identical.

In the example above, `ItemList` implements `IteratorAggregate<int, string>` and `ItemListInterface` which extends `Traversable<int, Item>`. Since `IteratorAggregate` also extends `Traversable`, the class inherits `Traversable` twice -- once with `TValue` as `string` (from `IteratorAggregate`) and once with `TValue` as `Item` (from `ItemListInterface`). This creates a conflict because the template type cannot be both `string` and `Item`.

## How to fix it

Ensure all inheritance paths specify the same type arguments for the shared interface:

```diff-php
 /**
- * @implements \IteratorAggregate<int, string>
+ * @implements \IteratorAggregate<int, Item>
  */
 final class ItemList implements \IteratorAggregate, ItemListInterface
 {
 	public function getIterator(): \Traversable
 	{
 		return new \ArrayIterator([]);
 	}
 }
```

Or restructure the class hierarchy to avoid implementing the same generic interface with different type arguments.
