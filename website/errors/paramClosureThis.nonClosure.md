---
title: "paramClosureThis.nonClosure"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @param-closure-this Foo $callback
	 */
	public function register(callable $callback): void
	{
	}
}
```

## Why is it reported?

The `@param-closure-this` PHPDoc tag is used on a parameter whose type is not `Closure`. The `@param-closure-this` tag specifies the `$this` context inside a Closure, so it only makes sense for parameters typed as `Closure`. Other callable types (like `callable`) do not bind a `$this` context.

## How to fix it

Change the parameter type to `Closure`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/**
 	 * @param-closure-this Foo $callback
 	 */
-	public function register(callable $callback): void
+	public function register(\Closure $callback): void
 	{
 	}
 }
```
