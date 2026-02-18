---
title: "parameter.byRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public function doFoo(int $i, int &$j): void
	{
	}
}

class Child extends Base
{
	public function doFoo(int &$i, int $j): void // error: Parameter #1 $i of method Child::doFoo() is passed
	{                                             //        by reference but parameter #1 $i of method
	}                                             //        Base::doFoo() is not passed by reference.
}
```

## Why is it reported?

When a child class overrides a method from a parent class or implements an interface method, the pass-by-reference semantics of each parameter must match. If the parent declares a parameter as pass-by-value but the child declares it as pass-by-reference (or vice versa), the method signature is incompatible. This violates the Liskov substitution principle and will cause a fatal error at runtime.

## How to fix it

Make the parameter's pass-by-reference declaration match the parent method.

```diff-php
 class Child extends Base
 {
-	public function doFoo(int &$i, int $j): void
+	public function doFoo(int $i, int &$j): void
 	{
 	}
 }
```
