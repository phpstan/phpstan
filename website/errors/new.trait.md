---
title: "new.trait"
shortDescription: "Traits cannot be instantiated with the new keyword."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public function doSomething(): void {}
}

$obj = new MyTrait(); // error: Cannot instantiate trait MyTrait.
```

## Why is it reported?

Traits in PHP are not standalone classes and cannot be instantiated with the `new` keyword. A trait is a mechanism for code reuse — it provides methods that can be included in one or more classes using the `use` keyword. Attempting to instantiate a trait will result in a fatal error at runtime.

## How to fix it

Use the trait inside a class and instantiate that class instead:

```diff-php
 trait MyTrait
 {
 	public function doSomething(): void {}
 }

+class MyClass
+{
+	use MyTrait;
+}
+
-$obj = new MyTrait();
+$obj = new MyClass();
```
