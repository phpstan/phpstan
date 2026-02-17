---
title: "staticClassAccess.privateMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private static function secret(): void
	{
	}

	public function doFoo(): void
	{
		static::secret();
	}
}
```

## Why is it reported?

A private method is called through `static::` in a non-final class. The `static::` keyword resolves at runtime to the actual class, which may be a child class. Since private methods are not inherited by child classes, calling a private method through `static::` can cause a fatal error if the method is invoked from a child class context.

## How to fix it

Use `self::` instead of `static::` for private method calls:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private static function secret(): void
 	{
 	}

 	public function doFoo(): void
 	{
-		static::secret();
+		self::secret();
 	}
 }
```

Or make the class `final`, or make the method `protected` or `public`.
