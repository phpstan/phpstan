---
title: "property.readOnlyAssignByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(
		public readonly int $value = 42,
	) {}

	public function doFoo(): void
	{
		$ref = &$this->value;
	}
}
```

## Why is it reported?

A `readonly` property cannot be assigned by reference. Creating a reference to a readonly property would allow its value to be modified through the reference variable, bypassing the readonly constraint. PHP prohibits this at the language level.

## How to fix it

Copy the value instead of creating a reference:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function __construct(
 		public readonly int $value = 42,
 	) {}

 	public function doFoo(): void
 	{
-		$ref = &$this->value;
+		$copy = $this->value;
 	}
 }
```
