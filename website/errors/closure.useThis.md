---
title: "closure.useThis"
shortDescription: "Closure attempts to import $this via the use clause."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$fn = static function () use ($this) {
			echo $this->bar();
		};
	}

	public function bar(): string
	{
		return 'bar';
	}
}
```

## Why is it reported?

The `$this` variable cannot be used as a lexical variable in a closure's `use` clause. PHP does not allow `use ($this)` -- it is a syntax error. Non-static closures already have access to `$this` automatically, so importing it is both unnecessary and invalid.

## How to fix it

Remove `$this` from the `use` clause and make the closure non-static so it has access to `$this`:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$fn = static function () use ($this) {
-			echo $this->bar();
+		$fn = function () {
+			echo $this->bar();
 		};
 	}

 	public function bar(): string
 	{
 		return 'bar';
 	}
 }
```
