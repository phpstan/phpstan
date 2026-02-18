---
title: "method.nonStatic"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static function doFoo(): void
	{
	}
}

class Bar extends Foo
{
	public function doFoo(): void
	{
	}
}
```

## Why is it reported?

A non-static method is overriding a static method from a parent class. PHP does not allow changing a method from static to non-static when overriding. The method signature in the child class must match the static/non-static nature of the parent method. Violating this produces a fatal error.

## How to fix it

Keep the method static in the child class:

```diff-php
 class Bar extends Foo
 {
-	public function doFoo(): void
+	public static function doFoo(): void
 	{
 	}
 }
```
