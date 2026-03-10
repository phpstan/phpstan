---
title: "trait.duplicateMethod"
shortDescription: "Method is declared more than once in the same trait."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public function doSomething(): void
	{
	}

	public function doSomething(): void
	{
	}
}

class Foo
{
	use MyTrait;
}
```

## Why is it reported?

A trait declares the same method more than once. PHP does not allow two methods with the same name in a single trait. This is a fatal error.

## How to fix it

Remove the duplicate method declaration, keeping only one:

```diff-php
 trait MyTrait
 {
 	public function doSomething(): void
 	{
 	}
-
-	public function doSomething(): void
-	{
-	}
 }
```

If the duplicate methods were intended to have different behavior, rename one of them:

```diff-php
 trait MyTrait
 {
 	public function doSomething(): void
 	{
 	}

-	public function doSomething(): void
+	public function doSomethingElse(): void
 	{
 	}
 }
```
