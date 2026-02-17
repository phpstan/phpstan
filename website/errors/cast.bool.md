---
title: "cast.bool"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
}

$foo = new Foo();
$value = (bool) $foo;
```

## Why is it reported?

The expression being cast to `bool` has a type that cannot be meaningfully cast to a boolean. Not all types support casting to `bool` in a well-defined way.

In the example above, an object of class `Foo` is cast to `bool`. While PHP allows casting most values to `bool`, PHPStan reports this when the source type makes the cast problematic or undefined.

## How to fix it

Instead of casting the value directly, use an explicit comparison or add a method that returns a boolean:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
+	public function isValid(): bool
+	{
+		return true;
+	}
 }

 $foo = new Foo();
-$value = (bool) $foo;
+$value = $foo->isValid();
```
