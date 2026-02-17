---
title: "staticProperty.nonStaticAccess"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public static int $count = 0;
}

$foo = new Foo();
echo $foo->count;
```

## Why is it reported?

A static property is being accessed using instance (non-static) syntax (`$obj->prop`). Static properties should be accessed using the class name and the `::` operator (`ClassName::$prop`).

## How to fix it

Use static access syntax:

```diff-php
 <?php declare(strict_types = 1);

 $foo = new Foo();
-echo $foo->count;
+echo Foo::$count;
```
