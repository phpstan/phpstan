---
title: "paramClosureThis.nonClosure"
shortDescription: "Tag @param-closure-this is used on a parameter that is not a Closure."
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
	public function register(string $callback): void
	{
	}
}
```

## Why is it reported?

The `@param-closure-this` PHPDoc tag is used on a parameter whose native type is not `Closure`. The `@param-closure-this` tag specifies the `$this` context inside a Closure, so it only makes sense for parameters typed as `Closure`. Other types (like `string`, `callable`, or `array`) do not bind a `$this` context in the same way.

## How to fix it

Change the parameter type to `Closure`:

```diff-php
 class Foo
 {
 	/**
 	 * @param-closure-this Foo $callback
 	 */
-	public function register(string $callback): void
+	public function register(\Closure $callback): void
 	{
 	}
 }
```

Or remove the `@param-closure-this` tag if the parameter is not intended to be a Closure:

```diff-php
 class Foo
 {
-	/**
-	 * @param-closure-this Foo $callback
-	 */
 	public function register(string $callback): void
 	{
 	}
 }
```
