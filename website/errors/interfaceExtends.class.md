---
title: "interfaceExtends.class"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class SomeClass
{
}

interface MyInterface extends SomeClass // ERROR: Interface MyInterface extends class SomeClass.
{
}
```

## Why is it reported?

An interface is attempting to extend a class using the `extends` keyword. In PHP, interfaces can only extend other interfaces. Classes cannot be used in an interface's `extends` clause. This is a language-level constraint that will cause a fatal error at runtime.

## How to fix it

If the class implements an interface, extend that interface instead:

```diff-php
 <?php declare(strict_types = 1);

+interface SomeInterface
+{
+	public function doSomething(): void;
+}
+
-class SomeClass
+class SomeClass implements SomeInterface
 {
+	public function doSomething(): void
+	{
+	}
 }

-interface MyInterface extends SomeClass
+interface MyInterface extends SomeInterface
 {
 }
```

If the intent is to define a contract, declare the required methods directly on the interface:

```diff-php
 <?php declare(strict_types = 1);

-interface MyInterface extends SomeClass
+interface MyInterface
 {
+	public function doSomething(): void;
 }
```
