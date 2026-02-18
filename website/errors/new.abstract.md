---
title: "new.abstract"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Foo
{
	abstract public function doSomething(): void;
}

$foo = new Foo(); // error: Instantiated class Foo is abstract.
```

## Why is it reported?

Abstract classes cannot be instantiated directly in PHP. They are meant to be extended by concrete (non-abstract) subclasses that implement all abstract methods. Attempting to use `new` on an abstract class will result in a fatal error at runtime.

## How to fix it

Create a concrete subclass that implements all abstract methods and instantiate that instead.

```diff-php
 abstract class Foo
 {
 	abstract public function doSomething(): void;
 }

+class ConcreteFoo extends Foo
+{
+	public function doSomething(): void
+	{
+		// implementation
+	}
+}
+
-$foo = new Foo();
+$foo = new ConcreteFoo();
```
