---
title: "parameter.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public function doSomething(): void
	{
	}
}

function doFoo(MyTrait $param): void // error: Parameter $param of function doFoo() has invalid type MyTrait.
{
}
```

## Why is it reported?

Traits cannot be used as type declarations in PHP. Unlike classes and interfaces, traits are a code reuse mechanism and do not define a type that can be checked at runtime. Using a trait as a parameter type will cause a fatal error. PHP only allows classes, interfaces, and built-in types in parameter type declarations.

## How to fix it

Extract an interface from the trait and use it as the parameter type instead.

```diff-php
+interface MyTraitInterface
+{
+	public function doSomething(): void;
+}
+
 trait MyTrait
 {
 	public function doSomething(): void
 	{
 	}
 }

-function doFoo(MyTrait $param): void
+function doFoo(MyTraitInterface $param): void
 {
 }
```
