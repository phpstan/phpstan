---
title: "argument.unresolvableType"
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
	public function add(mixed $item): void
	{
	}
}

function doFoo(Collection $collection): void
{
	$collection->add('hello');
}
```

## Why is it reported?

PHPStan was unable to resolve a template (generic) type in a parameter of the called function or method. This happens when a generic class is used without specifying its type parameters, making the resolved parameter type contain unresolvable template types.

In the example above, the `$collection` parameter is typed as `Collection` without specifying the template type `T`, so PHPStan cannot determine what type the `add()` method expects.

## How to fix it

Specify the generic type parameter in the PHPDoc:

```diff-php
-function doFoo(Collection $collection): void
+/** @param Collection<string> $collection */
+function doFoo(Collection $collection): void
 {
 	$collection->add('hello');
 }
```
