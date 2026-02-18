---
title: "callable.shadowTemplate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Collection
{
	/**
	 * @param callable<T>(T): T $callback
	 */
	public function map(callable $callback): void
	{
	}
}
```

## Why is it reported?

A generic callable type in a PHPDoc tag defines a template type parameter with the same name as an existing template on the enclosing function or class. In the example above, both the class and the `map` method define a template named `T`, which creates ambiguity about which `T` is being referred to inside the callable type.

## How to fix it

Rename the template type parameter in the callable to avoid shadowing:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
  */
 class Collection
 {
 	/**
-	 * @param callable<T>(T): T $callback
+	 * @param callable<U>(U): U $callback
 	 */
 	public function map(callable $callback): void
 	{
 	}
 }
```
