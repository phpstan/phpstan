---
title: "method.templateTypeNotInParameter"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Factory
{
	/**
	 * @template T of object
	 * @param string $class
	 * @return T
	 */
	public function create(string $class): object
	{
		return new $class();
	}
}
```

## Why is it reported?

A template type declared on a method is not referenced in any of its parameters. PHPStan cannot infer the concrete type for the template type `T` from the call site because no parameter uses it. This means the generic type information is useless -- callers will always get the upper bound of the template type instead of a specific type.

In the example above, `T` appears in the `@return` tag but not in any `@param` tag. PHPStan has no way to determine what `T` should be when the method is called.

## How to fix it

Reference the template type in a parameter so PHPStan can infer it from the argument:

```diff-php
 <?php declare(strict_types = 1);

 class Factory
 {
 	/**
 	 * @template T of object
-	 * @param string $class
+	 * @param class-string<T> $class
 	 * @return T
 	 */
 	public function create(string $class): object
 	{
 		return new $class();
 	}
 }
```

If the template type is genuinely not needed, remove it:

```diff-php
 <?php declare(strict_types = 1);

 class Factory
 {
 	/**
-	 * @template T of object
-	 * @param string $class
-	 * @return T
+	 * @param class-string $class
+	 * @return object
 	 */
 	public function create(string $class): object
 	{
 		return new $class();
 	}
 }
```
