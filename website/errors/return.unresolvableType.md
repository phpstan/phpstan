---
title: "return.unresolvableType"
shortDescription: "PHPDoc @return tag contains a type that cannot be resolved."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo {}
class Bar {}

class MyClass
{
	/**
	 * @return Foo&Bar
	 */
	public function get()
	{
		return new Foo();
	}
}
```

## Why is it reported?

The PHPDoc `@return` tag contains a type that PHPStan cannot resolve. This happens when the type evaluates to an impossible type, uses invalid type syntax, or references types that create contradictions.

In the example above, `Foo&Bar` is an intersection type, but since `Foo` and `Bar` are unrelated classes (neither extends the other), no value can be both `Foo` and `Bar` at the same time. PHPStan resolves this to an impossible type and reports it as unresolvable.

## How to fix it

Use a valid return type. If the method should return an object implementing multiple interfaces, use interface types:

```diff-php
+interface FooInterface {}
+interface BarInterface {}
+
 class MyClass
 {
 	/**
-	 * @return Foo&Bar
+	 * @return FooInterface&BarInterface
 	 */
 	public function get()
```

Or use a concrete type:

```diff-php
 /**
- * @return Foo&Bar
+ * @return Foo
  */
 public function get()
```
