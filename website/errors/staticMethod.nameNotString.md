---
title: "staticMethod.nameNotString"
shortDescription: "Dynamic static method name in Foo::{$name}() is not a string."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static function doFoo(): void
	{
	}

	public static function test(int $name): void
	{
		self::$name();
	}
}
```

## Why is it reported?

When calling a static method dynamically with the `Foo::{$name}()` or `self::$name()` syntax, the `$name` expression must be a string. Static method names are not cast to string at runtime, so even an object implementing `Stringable` is not accepted — only an actual `string` works. In the example above, `$name` is an `int`, which cannot be used as a method name and results in an error.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Ensure the dynamic method name expression is a string:

```diff-php
-	public static function test(int $name): void
+	public static function test(string $name): void
 	{
 		self::$name();
 	}
```

If the value comes from a `Stringable` object, cast it to `string` first:

```diff-php
 	public static function test(\Stringable $name): void
 	{
-		self::$name();
+		self::{(string) $name}();
 	}
```
