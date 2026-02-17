---
title: "method.void"
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
$result = $foo->doFoo();
```

## Why is it reported?

The return value of a method that returns `void` is being used. A `void` return type indicates that the method does not return a meaningful value. Using the result of such a call is a logic error because the value is always `null`.

## How to fix it

Do not use the return value of a void method:

```diff-php
 <?php declare(strict_types = 1);

 $foo = new Foo();
-$result = $foo->doFoo();
+$foo->doFoo();
```

If you need the method to return a value, change its return type.
