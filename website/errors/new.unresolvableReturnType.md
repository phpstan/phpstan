---
title: "new.unresolvableReturnType"
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
	public function __construct(private mixed $item)
	{
	}

	/** @return T */
	public function first(): mixed
	{
		return $this->item;
	}
}

function doFoo(): void
{
	$collection = new Collection(); // ERROR: Unable to resolve the return type of constructor of class Collection.
	$collection->first();
}
```

## Why is it reported?

The constructor of the class uses generic template types in its return type (or the class is generic), but PHPStan cannot resolve those template types from the arguments passed to the constructor. This means that the type information flowing from the `new` expression into subsequent method calls or property accesses will be incomplete, which may lead to inaccurate analysis.

This typically happens when a generic class constructor expects arguments that help PHPStan infer the template types, but those arguments are missing or have types that are too broad.

## How to fix it

Pass arguments to the constructor that allow PHPStan to resolve the template types:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$collection = new Collection();
+	$collection = new Collection('hello');
 	$collection->first(); // PHPStan now knows this returns string
 }
```

Or specify the template type explicitly using a PHPDoc `@var` annotation on the variable:

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	/** @var Collection<string> $collection */
	$collection = new Collection();
}
```
