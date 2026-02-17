---
title: "offsetAssign.valueType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class TypedCollection implements ArrayAccess
{
	/** @var array<int, string> */
	private array $items = [];

	public function offsetExists(mixed $offset): bool { return isset($this->items[$offset]); }
	public function offsetGet(mixed $offset): mixed { return $this->items[$offset]; }
	public function offsetSet(mixed $offset, mixed $value): void { $this->items[$offset] = $value; }
	public function offsetUnset(mixed $offset): void { unset($this->items[$offset]); }
}

function doFoo(TypedCollection $collection): void
{
	$collection[] = 123; // ERROR: TypedCollection does not accept int.
}
```

## Why is it reported?

The value being assigned to an offset on the object is not compatible with the type that the object accepts. The object implements `ArrayAccess` (or has similar offset-access capabilities), but the value being assigned does not match the expected type. In the example above, the collection expects `string` values but receives an `int`.

## How to fix it

Assign a value of the correct type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(TypedCollection $collection): void
 {
-	$collection[] = 123;
+	$collection[] = 'hello';
 }
```

Or update the collection's type to accept the value type being assigned:

```diff-php
 <?php declare(strict_types = 1);

 class TypedCollection implements ArrayAccess
 {
-	/** @var array<int, string> */
+	/** @var array<int, string|int> */
 	private array $items = [];

 	// ...
 }
```
