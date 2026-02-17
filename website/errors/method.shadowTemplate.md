---
title: "method.shadowTemplate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T of \Exception
 */
class ErrorHandler
{
	/**
	 * @template T
	 * @param T $value
	 * @return T
	 */
	public function handle($value)
	{
		return $value;
	}
}
```

## Why is it reported?

A method declares a `@template` type parameter with the same name as one already declared on its containing class. The method-level template type `T` shadows the class-level template type `T`, which makes it impossible to reference the class template type from within the method. This is confusing and likely a mistake.

In the example above, the class declares `@template T of \Exception`, but the method also declares `@template T` (without the bound). Inside the method, `T` refers to the method's template type, not the class-level one.

## How to fix it

Rename the method-level template type to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T of \Exception
  */
 class ErrorHandler
 {
 	/**
-	 * @template T
-	 * @param T $value
-	 * @return T
+	 * @template U
+	 * @param U $value
+	 * @return U
 	 */
 	public function handle($value)
 	{
 		return $value;
 	}
 }
```

If the method intends to use the class-level template type, remove the `@template` declaration from the method:

```diff-php
 /**
  * @template T of \Exception
  */
 class ErrorHandler
 {
 	/**
-	 * @template T
 	 * @param T $value
 	 * @return T
 	 */
 	public function handle($value)
 	{
 		return $value;
 	}
 }
```
