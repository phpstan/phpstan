---
title: "trait.duplicateMethod"
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
```

## Why is it reported?

A trait declares the same method more than once. PHP does not allow two methods with the same name in a single trait. This is a fatal error.

In the example above, the method `doSomething()` is declared twice in the trait `MyTrait`.

## How to fix it

Remove the duplicate method declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

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
 <?php declare(strict_types = 1);

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
