---
title: "staticMethod.private"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private static function computeSecret(): int
	{
		return 42;
	}
}

class Bar
{
	public function doFoo(): int
	{
		return Foo::computeSecret();
	}
}
```

## Why is it reported?

The code calls a private static method from a class that is not the declaring class. Private methods are only accessible from within the class that defines them. They are not accessible from subclasses or any other class. This results in a fatal error at runtime.

## How to fix it

If the method needs to be called from outside the class, change its visibility to public or protected:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private static function computeSecret(): int
+	public static function computeSecret(): int
 	{
 		return 42;
 	}
 }
```

Alternatively, expose the functionality through a public method:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private static function computeSecret(): int
 	{
 		return 42;
 	}
+
+	public static function getResult(): int
+	{
+		return self::computeSecret();
+	}
 }

 class Bar
 {
 	public function doFoo(): int
 	{
-		return Foo::computeSecret();
+		return Foo::getResult();
 	}
 }
```
