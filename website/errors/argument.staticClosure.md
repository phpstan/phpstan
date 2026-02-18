---
title: "argument.staticClosure"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(callable $callback): void
	{
	}

	/**
	 * @param Closure(this): void $callback
	 */
	public function doBar(Closure $callback): void
	{
	}
}

$foo = new Foo();
$foo->doBar(static function (): void {
	// ...
});
```

## Why is it reported?

The parameter expects a bindable closure (one that can access `$this`), but a static closure was passed. Static closures declared with the `static` keyword cannot be bound to an object, so they cannot access `$this`. When a parameter is type-hinted with `Closure(this): ...` or otherwise expects to bind `$this` inside the closure, passing a static closure will fail at runtime.

## How to fix it

Remove the `static` keyword from the closure:

```diff-php
-$foo->doBar(static function (): void {
+$foo->doBar(function (): void {
 	// ...
 });
```
