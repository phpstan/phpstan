---
title: "property.readOnlyByPhpDocAssignNotInConstructor"
shortDescription: "@readonly property is assigned outside of the constructor."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @readonly */
	private int $value;

	public function __construct(int $value)
	{
		$this->value = $value; // OK
	}

	public function update(int $newValue): void
	{
		$this->value = $newValue; // ERROR
	}
}
```

## Why is it reported?

The property is annotated with `@readonly` in its PHPDoc, which means it should only be assigned in the constructor. Assigning it outside the constructor violates the immutability contract declared by the `@readonly` tag.

Assigning the property in a helper method is allowed when that method is only ever run from the constructor: a `private` method (or a `protected` method of a `final` class without a parent) that the class calls on `$this` only from its constructor, directly or through other such methods. The assignment is reported when the method can also be called from elsewhere, for example from another public method.

## How to fix it

Move the assignment to the constructor, or use a pattern that returns a new instance with the updated value:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @readonly */
 	private int $value;

 	public function __construct(int $value)
 	{
 		$this->value = $value;
 	}

-	public function update(int $newValue): void
+	public function withValue(int $newValue): self
 	{
-		$this->value = $newValue;
+		return new self($newValue);
 	}
 }
```

If the assignment happens in a helper method, make sure only the constructor calls it:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @readonly */
 	private string $foo;

 	public function __construct()
 	{
 		$this->init();
 	}

-	public function reset(): void
-	{
-		$this->init();
-	}
-
 	private function init(): void
 	{
 		$this->foo = 'bar';
 	}
 }
```
