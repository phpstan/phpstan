---
title: "property.nestedUnusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Processor
{
	/** @var array<int, string|int> */
	private array $items = []; // ERROR: Type array<int, int|string> of property Processor::$items can be narrowed to array<int, string>.

	public function add(string $item): void
	{
		$this->items[] = $item;
	}
}
```

## Why is it reported?

The property has a declared type that is wider than what is actually assigned to it. PHPStan analyses all assignments to the property and determines that a type within a nested position of the declared type is never actually assigned. In the example above, the property declares it can hold `string|int` values, but only `string` values are ever assigned.

This helps catch overly broad type declarations that make the code harder to reason about and can hide bugs.

## How to fix it

Narrow the property type to match what is actually assigned:

```diff-php
 <?php declare(strict_types = 1);

 class Processor
 {
-	/** @var array<int, string|int> */
+	/** @var array<int, string> */
 	private array $items = [];

 	public function add(string $item): void
 	{
 		$this->items[] = $item;
 	}
 }
```

If the broader type is intentional because future code will assign values of additional types, add the missing assignment or adjust the design accordingly.
