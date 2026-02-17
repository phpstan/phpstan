---
title: "method.alreadyNarrowedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Collection
{
	/** @var array<int, string> */
	private array $items = [];

	public function has(string $key): bool
	{
		return isset($this->items[$key]);
	}
}

function doFoo(Collection $collection): void
{
	if ($collection->has('foo')) {
		// ...
	} elseif ($collection->has('foo')) { // ERROR: will always evaluate to true
		// ...
	}
}
```

## Why is it reported?

A method call that acts as a type-checking or type-narrowing operation will always evaluate to true. PHPStan has already inferred enough type information to determine that the result of this method call is always `true` in this context.

This typically occurs when the same type check is performed redundantly, or when previous conditions already guarantee the result.

## How to fix it

Remove the redundant check, or fix the logic to test for a different condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Collection $collection): void
 {
 	if ($collection->has('foo')) {
 		// ...
-	} elseif ($collection->has('foo')) {
+	} elseif ($collection->has('bar')) {
 		// ...
 	}
 }
```

If the check is no longer needed in an `elseif`/`else` chain, remove remaining cases below and the error will disappear.
