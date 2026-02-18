---
title: "property.readOnlyByPhpDocAssignByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @var int
	 * @readonly
	 */
	public $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function doFoo(): void
	{
		$ref = &$this->value; // ERROR: @readonly property Foo::$value is assigned by reference.
	}
}
```

## Why is it reported?

A property marked with the `@readonly` PHPDoc annotation is being assigned by reference. When a variable holds a reference to a property, the property's value can be changed through that reference, which violates the `@readonly` contract. Even if the code does not intend to modify the property through the reference, the reference itself creates a pathway for mutation.

## How to fix it

Read the property value into a regular variable instead of creating a reference:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$ref = &$this->value;
+		$ref = $this->value;
 	}
 }
```

If the property should actually be mutable, remove the `@readonly` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/**
 	 * @var int
-	 * @readonly
 	 */
 	public $value;
 }
```
