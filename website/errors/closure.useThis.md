---
title: "closure.useThis"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$self = $this;
		$fn = function () use ($self) {
			$self->doSomething();
		};
	}

	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

The closure uses `$this` assigned to another variable in its `use` clause. Non-static closures in PHP already have access to `$this` automatically, so passing it through a variable is unnecessary and less clear.

This can also be reported when `$this` is directly placed in the `use` clause (`use ($this)`), which is a PHP syntax error.

## How to fix it

Use `$this` directly inside the closure body:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$self = $this;
-		$fn = function () use ($self) {
-			$self->doSomething();
+		$fn = function () {
+			$this->doSomething();
 		};
 	}

 	public function doSomething(): void
 	{
 	}
 }
```
