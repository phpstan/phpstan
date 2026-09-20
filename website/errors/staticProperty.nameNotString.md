---
title: "staticProperty.nameNotString"
shortDescription: "Dynamic static property name in Foo::${$name} is not a string."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static string $bar = '';

	public function test(array $name): void
	{
		echo self::${$name};
	}
}
```

## Why is it reported?

When accessing a static property dynamically with the `Foo::${$name}` syntax, the `$name` expression must produce a string. PHP casts the name to string at runtime, so a `string`, an `int`, or an object implementing `Stringable` are all accepted. Values that cannot be cast to a string — such as an `array` or a plain `object` — are invalid and result in an error. In the example above, `$name` is an `array`, which cannot be used as a property name.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Ensure the dynamic static property name can be cast to a string:

```diff-php
-	public function test(array $name): void
+	public function test(string $name): void
 	{
 		echo self::${$name};
 	}
```

If the property name is known, access it directly instead:

```diff-php
 	public function test(array $name): void
 	{
-		echo self::${$name};
+		echo self::$bar;
 	}
```
