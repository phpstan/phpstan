---
title: "method.childReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Animal {}
class Dog extends Animal {}
class Cat extends Animal {}

class Base
{
	/** @return Dog */
	public function get()
	{
	}
}

class Child extends Base
{
	/** @return Cat */ // ERROR: Return type (Cat) of method Child::get() should be compatible with return type (Dog) of method Base::get()
	public function get()
	{
	}
}
```

## Why is it reported?

When a child class overrides a method from a parent class (or implements an interface method), the return type of the overriding method must be covariant with the return type of the parent method. This is known as the Liskov Substitution Principle: anywhere the parent class is used, the child class should work as a drop-in replacement.

A covariant return type means the child method can return the same type or a more specific subtype. In this example, `Cat` is not a subtype of `Dog`, so the return type is incompatible.

When the parent method returns `Dog`, the overriding method could return `Dog` or a subclass of `Dog`, but not a completely unrelated type like `Cat`.

## How to fix it

Make the return type of the child method compatible with the parent:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	/** @return Cat */
+	/** @return Dog */
 	public function get()
 	{
 	}
 }
```

Or widen the return type in the parent class if both types should be allowed:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
-	/** @return Dog */
+	/** @return Animal */
 	public function get()
 	{
 	}
 }
```
