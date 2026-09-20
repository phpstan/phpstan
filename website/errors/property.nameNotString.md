---
title: "property.nameNotString"
shortDescription: "Dynamic property name is not a string."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $bar = "hello";
}

function doFoo(Foo $foo): void
{
	$name = ["bar"];
	echo $foo->$name;
}
```

## Why is it reported?

When accessing an object property dynamically using the `$obj->$name` syntax, the `$name` expression must produce a string. PHP casts the name to string at runtime, so a `string`, an `int`, or an object implementing `Stringable` are all accepted. Values that cannot be cast to a string — such as an `array` or a plain `object` — are invalid and result in an error.

In the example above, the variable `$name` is an array (`["bar"]`), which is not a valid property name.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Ensure the dynamic property name is a string:

```diff-php
 function doFoo(Foo $foo): void
 {
-	$name = ["bar"];
+	$name = "bar";
 	echo $foo->$name;
 }
```

Or use a direct property access if the property name is known:

```diff-php
 function doFoo(Foo $foo): void
 {
-	$name = ["bar"];
-	echo $foo->$name;
+	echo $foo->bar;
 }
```
