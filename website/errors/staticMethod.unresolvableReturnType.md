---
title: "staticMethod.unresolvableReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Factory
{
	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @return T
	 */
	public static function create(string $class): object
	{
		return new $class();
	}
}

function doFoo(): void
{
	$result = Factory::create('stdClass' | 'DateTime');
}
```

## Why is it reported?

The return type of a static method call contains a template type that PHPStan cannot resolve based on the provided arguments. This typically happens when the arguments passed to the method do not provide enough type information for PHPStan to determine the concrete type that the template parameter should resolve to.

When PHPStan cannot resolve the template type, the return type becomes imprecise, which can lead to missed type errors downstream.

## How to fix it

Pass more specific types as arguments so that PHPStan can resolve the template type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$result = Factory::create('stdClass' | 'DateTime');
+	$result = Factory::create(\stdClass::class);
 }
```

If the template type on the method is not needed, simplify the method signature:

```diff-php
 <?php declare(strict_types = 1);

 class Factory
 {
-	/**
-	 * @template T of object
-	 * @param class-string<T> $class
-	 * @return T
-	 */
-	public static function create(string $class): object
+	/**
+	 * @param class-string $class
+	 */
+	public static function create(string $class): object
 	{
 		return new $class();
 	}
 }
```
