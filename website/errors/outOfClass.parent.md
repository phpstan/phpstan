---
title: "outOfClass.parent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo parent::FOO; // ERROR: Using parent outside of class scope.
```

## Why is it reported?

The keyword `parent` refers to the parent class of the class in which it is used. It can only appear inside a class definition that extends another class. Using `parent` outside of a class scope (for example, in a function or in the global scope) is a PHP error because there is no class context to resolve the parent from.

This error is reported in various contexts: accessing constants (`parent::FOO`), calling static methods (`parent::method()`), accessing static properties (`parent::$prop`), using `new parent()`, or using `parent` in an `instanceof` check.

## How to fix it

Use the fully qualified class name instead of `parent` when outside a class:

```diff-php
 <?php declare(strict_types = 1);

-echo parent::FOO;
+echo BaseClass::FOO;
```

Or move the code inside a class method where `parent` is valid:

```php
<?php declare(strict_types = 1);

class BaseClass
{
	public const FOO = 'bar';
}

class ChildClass extends BaseClass
{
	public function doFoo(): void
	{
		echo parent::FOO; // This works inside a class that extends another
	}
}
```
