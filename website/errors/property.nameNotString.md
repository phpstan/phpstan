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

When accessing an object property dynamically using the `$obj->$name` syntax, the `$name` expression must evaluate to a string. If the value used as the property name is not a string (for example, an array or object), the property access is invalid and will produce unexpected behavior or errors at runtime.

In the example above, the variable `$name` is an array (`["bar"]`), which is not a valid property name.

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
