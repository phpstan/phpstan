---
title: "property.readOnlyByPhpDocAssignNotInConstructor"
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
