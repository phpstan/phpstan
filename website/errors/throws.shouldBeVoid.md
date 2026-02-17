---
title: "throws.shouldBeVoid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	/**
	 * @throws void
	 */
	public function doFoo(): void
	{
	}
}

class ChildClass extends ParentClass
{
	/**
	 * @throws \RuntimeException
	 */
	public function doFoo(): void
	{
		throw new \RuntimeException();
	}
}
```

## Why is it reported?

A child method declares a `@throws` type, but the parent method explicitly has `@throws void` in its PHPDoc. The `@throws void` annotation means the method promises not to throw any exceptions. A child method that throws exceptions violates this contract.

## How to fix it

Remove the exception throwing from the child method:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	/**
-	 * @throws \RuntimeException
-	 */
 	public function doFoo(): void
 	{
-		throw new \RuntimeException();
 	}
 }
```

Or remove the `@throws void` annotation from the parent method if it should allow child methods to throw exceptions.
