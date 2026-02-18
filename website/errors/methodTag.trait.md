---
title: "methodTag.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public function doFoo(): void
	{
	}
}

/**
 * @method MyTrait getHelper()
 */
class Foo
{
}
```

## Why is it reported?

A `@method` PHPDoc tag references a trait as a type. Traits cannot be used as types in PHP -- they cannot be instantiated, passed as parameters, or returned from methods. A trait is a mechanism for code reuse and does not represent a type on its own.

## How to fix it

Replace the trait with a class or interface that represents the intended type:

```diff-php
-trait MyTrait
+interface MyInterface
 {
-	public function doFoo(): void
-	{
-	}
+	public function doFoo(): void;
 }

 /**
- * @method MyTrait getHelper()
+ * @method MyInterface getHelper()
  */
 class Foo
 {
 }
```
