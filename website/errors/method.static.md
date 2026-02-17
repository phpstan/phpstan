---
title: "method.static"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public function doFoo(): void
	{
	}
}

class ChildClass extends ParentClass
{
	public static function doFoo(): void
	{
	}
}
```

## Why is it reported?

A static method overrides a non-static method from a parent class. PHP does not allow changing a non-static method to static in a child class. This will cause a fatal error at runtime.

## How to fix it

Keep the method non-static in the child class:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	public static function doFoo(): void
+	public function doFoo(): void
 	{
 	}
 }
```
