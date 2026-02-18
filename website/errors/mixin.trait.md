---
title: "mixin.trait"
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
 * @mixin MyTrait
 */
class Foo
{
}
```

## Why is it reported?

A `@mixin` PHPDoc tag references a trait. Traits cannot be used as types in PHP -- they cannot be instantiated or used as a mixin type. The `@mixin` tag expects a class or object type so that PHPStan knows which methods and properties to forward.

To use a trait's methods in a class, use the `use` statement directly in the class body instead of `@mixin`.

## How to fix it

Use the trait directly with a `use` statement:

```diff-php
-/**
- * @mixin MyTrait
- */
 class Foo
 {
+	use MyTrait;
 }
```

Alternatively, replace the trait reference with a class or interface:

```diff-php
-trait MyTrait
+class MyHelper
 {
 	public function doFoo(): void
 	{
 	}
 }

 /**
- * @mixin MyTrait
+ * @mixin MyHelper
  */
 class Foo
 {
 }
```
