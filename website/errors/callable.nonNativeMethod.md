---
title: "callable.nonNativeMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @method void doBar()
 */
class Foo
{
	public function doFoo(): void
	{
		$callable = $this->doBar(...);
	}

	public function __call(string $name, array $arguments): mixed
	{
		return null;
	}
}
```

## Why is it reported?

A first-class callable is being created from a method that is not natively declared in the class. The method exists only through PHPDoc `@method` annotations or magic `__call`/`__callStatic` methods. Creating a callable from such a method is unreliable because PHP resolves first-class callables at runtime based on native method declarations, and the behaviour with magic methods may not work as expected.

In the example above, `doBar` is defined via a `@method` PHPDoc tag and handled by `__call`, so `$this->doBar(...)` creates a callable from a non-native method.

## How to fix it

Declare the method natively in the class:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @method void doBar()
- */
 class Foo
 {
 	public function doFoo(): void
 	{
 		$callable = $this->doBar(...);
 	}

-	public function __call(string $name, array $arguments): mixed
+	public function doBar(): void
 	{
-		return null;
 	}
 }
```

Or use a closure wrapper instead of the first-class callable syntax:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @method void doBar()
  */
 class Foo
 {
 	public function doFoo(): void
 	{
-		$callable = $this->doBar(...);
+		$callable = function () { $this->doBar(); };
 	}

 	public function __call(string $name, array $arguments): mixed
 	{
 		return null;
 	}
 }
```
