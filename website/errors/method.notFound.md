---
title: "method.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}
}

$foo = new Foo();
$foo->doBar();
```

## Why is it reported?

A method is called on an object, but the method does not exist on that object's type. In the example above, `doBar()` is called on a `Foo` object, but only `doFoo()` is defined.

## How to fix it

Call a method that exists on the class:

```diff-php
 <?php declare(strict_types = 1);

 $foo = new Foo();
-$foo->doBar();
+$foo->doFoo();
```

Or add the missing method to the class.
