---
title: "outOfClass.self"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo self::FOO; // ERROR: Using self outside of class scope.
```

## Why is it reported?

The keyword `self` refers to the class in which it is used. It can only be used inside a class definition (in methods, class constants, or property declarations). Using `self` outside of a class scope (for example, in a function or in the global scope) is a PHP error because there is no class to reference.

## How to fix it

Use the fully qualified class name instead of `self` when outside a class:

```diff-php
 <?php declare(strict_types = 1);

-echo self::FOO;
+echo MyClass::FOO;
```

Or move the code inside a class method:

```php
<?php declare(strict_types = 1);

class MyClass
{
	public const FOO = 'bar';

	public function doFoo(): void
	{
		echo self::FOO; // This works inside a class
	}
}
```
