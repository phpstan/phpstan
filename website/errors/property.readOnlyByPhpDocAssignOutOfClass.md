---
title: "property.readOnlyByPhpDocAssignOutOfClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @readonly */
	public int $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}
}

class Bar
{
	public function modify(Foo $foo): void
	{
		$foo->value = 42;
	}
}
```

## Why is it reported?

The property is marked as `@readonly` in its PHPDoc, which means it should only be assigned inside the constructor of its declaring class. Code outside the declaring class is attempting to assign a value to this property, which violates the readonly contract.

The `@readonly` PHPDoc annotation is a PHPStan-enforced convention that mirrors the behavior of PHP's native `readonly` keyword but works on older PHP versions or in cases where the native keyword cannot be used.

## How to fix it

Move the assignment into the declaring class's constructor or remove it:

```diff-php
 <?php declare(strict_types = 1);

 class Bar
 {
-	public function modify(Foo $foo): void
-	{
-		$foo->value = 42;
-	}
+	public function createFoo(): Foo
+	{
+		return new Foo(42);
+	}
 }
```

If the property genuinely needs to be writable from outside the class, remove the `@readonly` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/** @readonly */
 	public int $value;

 	public function __construct(int $value)
 	{
 		$this->value = $value;
 	}
 }
```
