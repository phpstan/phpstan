---
title: "classConstant.nativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface FooInterface
{
	const string VERSION = '1.0';
}

class Foo implements FooInterface
{
	const int VERSION = 1;
}
```

## Why is it reported?

The native type of a class constant is not covariant with the native type of the constant it overrides. When a class or interface declares a typed constant and a child class overrides it, the overriding constant's native type must be compatible (covariant) with the parent's type. This is a PHP constraint enforced at runtime.

In the example above, `FooInterface::VERSION` has a native type of `string`, but `Foo::VERSION` overrides it with a native type of `int`, which is not a subtype of `string`.

## How to fix it

Make the overriding constant's native type compatible with the parent's type:

```diff-php
 <?php declare(strict_types = 1);

 interface FooInterface
 {
 	const string VERSION = '1.0';
 }

 class Foo implements FooInterface
 {
-	const int VERSION = 1;
+	const string VERSION = '2.0';
 }
```
