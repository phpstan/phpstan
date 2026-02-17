---
title: "method.unresolvableReturnType"
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
	/**
	 * @param T $item
	 * @return T
	 */
	public function wrap($item)
	{
		return $item;
	}
}

/** @var Collection<string> $collection */
$collection = new Collection();
$result = $collection->wrap(123);
```

## Why is it reported?

The return type of a method call contains an unresolvable type. This usually happens with generic (templated) methods where the template type cannot be resolved based on the arguments provided. PHPStan cannot determine what the actual return type will be, which reduces the quality of type analysis for subsequent code.

## How to fix it

Ensure that the arguments passed to the method provide enough type information for PHPStan to resolve the template types. This typically means passing arguments that match the expected template parameter types.

```diff-php
 <?php declare(strict_types = 1);

 /** @var Collection<string> $collection */
 $collection = new Collection();
-$result = $collection->wrap(123);
+$result = $collection->wrap('hello');
```
