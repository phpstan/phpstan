---
title: "property.nameNotString"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $bar = 'hello';
}

function doFoo(Foo $foo): void
{
	$name = 123;
	echo $foo->$name;
}
```

## Why is it reported?

When accessing an object property dynamically using the `$obj->$name` syntax, the `$name` expression must evaluate to a string. If the value used as the property name is not a string and cannot be converted to a string (for example, an integer, array, or object without `__toString()`), the property access is invalid and will produce unexpected behavior or errors at runtime.

In the example above, the variable `$name` is an integer (`123`), which is not a valid property name.

## How to fix it

Ensure the dynamic property name is a string:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Foo $foo): void
 {
-	$name = 123;
+	$name = 'bar';
 	echo $foo->$name;
 }
```

Or use a direct property access if the property name is known:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Foo $foo): void
 {
-	$name = 123;
-	echo $foo->$name;
+	echo $foo->bar;
 }
```
