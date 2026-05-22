---
title: "method.abstractOverridingNonAbstract"
shortDescription: "An abstract method overrides a non-abstract method from a parent class."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class ParentClass
{
	public function foo(): int
	{
		return 42;
	}
}

abstract class ChildClass extends ParentClass
{
	abstract public function foo(): int;
}
```

## Why is it reported?

PHP does not allow redeclaring a concrete (non-abstract) method as abstract in a child class. The parent class already provides an implementation of the method, and making it abstract in a subclass would remove that implementation, which is a fatal error at runtime.

This also applies to constructors, static methods, and methods inherited through multiple levels of inheritance.

## How to fix it

If the child class needs a different implementation, override the method with a concrete body instead of making it abstract:

```diff-php
 abstract class ChildClass extends ParentClass
 {
-	abstract public function foo(): int;
+	public function foo(): int
+	{
+		return 0;
+	}
 }
```

If the intent is to force subclasses to implement the method, consider using an interface instead:

```diff-php
 interface HasFoo
 {
 	public function foo(): int;
 }

-abstract class ChildClass extends ParentClass
+abstract class ChildClass extends ParentClass implements HasFoo
 {
-	abstract public function foo(): int;
 }
```
