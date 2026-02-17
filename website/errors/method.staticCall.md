---
title: "method.staticCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}
}

Foo::doFoo();
```

## Why is it reported?

An instance method is being called statically using the `::` operator. The method `doFoo()` is not declared as `static`, so it requires an object instance to be called on. Calling it statically is deprecated in modern PHP versions and can lead to errors if the method uses `$this`.

## How to fix it

Call the method on an instance of the class:

```diff-php
-Foo::doFoo();
+$foo = new Foo();
+$foo->doFoo();
```

If the method does not use `$this` and is intended to be called statically, declare it as `static`:

```diff-php
 class Foo
 {
-	public function doFoo(): void
+	public static function doFoo(): void
 	{
 	}
 }
```
