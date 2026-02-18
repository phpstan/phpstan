---
title: "method.variance"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class C
{
	/**
	 * @template-covariant U
	 * @return void
	 */
	public function doFoo(): void
	{
	}
}
```

## Why is it reported?

Variance annotations (`@template-covariant` and `@template-contravariant`) are only allowed for type parameters of classes and interfaces, not for methods or functions. A method-level template type cannot be declared covariant or contravariant because variance only has meaning in the context of a class's type parameters -- it describes how the class relates to its subtypes.

## How to fix it

Remove the variance annotation from the method-level template type. Use a plain `@template` instead:

```diff-php
 class C
 {
 	/**
-	 * @template-covariant U
+	 * @template U
 	 * @return void
 	 */
 	public function doFoo(): void
 	{
 	}
 }
```

If variance is needed, move the template type to the class level:

```diff-php
+/**
+ * @template-covariant U
+ */
 class C
 {
-	/**
-	 * @template-covariant U
-	 * @return void
-	 */
 	public function doFoo(): void
 	{
 	}
 }
```
