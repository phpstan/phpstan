---
title: "argument.staticClosure"
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
	public function doFoo(Closure $callback): void
	{
	}
}

$foo = new Foo();
$foo->doFoo(static function (): void {
	// ...
});
```

## Why is it reported?

The parameter expects a bindable closure (one that can access `$this`), but a static closure was passed. Static closures declared with the `static` keyword cannot be bound to an object, so they cannot access `$this`.

A method advertises that its parameter needs a bindable closure by using the [`@param-closure-this`](/writing-php-code/phpdocs-basics#callables) PHPDoc tag. Passing a static closure to such a parameter will fail at runtime.

## How to fix it

Remove the `static` keyword from the closure:

```diff-php
-$foo->doFoo(static function (): void {
+$foo->doFoo(function (): void {
 	// ...
 });
```
