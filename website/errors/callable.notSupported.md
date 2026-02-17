---
title: "callable.notSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$callable = $this->doFoo(...);
	}
}
```

## Why is it reported?

First-class callable syntax (`foo(...)`) was introduced in PHP 8.1. When PHPStan is configured to analyse code for a PHP version earlier than 8.1, using this syntax is not valid and will cause a syntax error at runtime.

This error is also reported when trying to create a callable from the `new` operator (e.g., `new Foo(...)`), which is not supported in any PHP version.

## How to fix it

Use a `Closure::fromCallable()` call or a closure wrapper instead of the first-class callable syntax:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$callable = $this->doFoo(...);
+		$callable = Closure::fromCallable([$this, 'doFoo']);
 	}
 }
```

Or configure PHPStan to analyse the code for PHP 8.1 or later by setting the `phpVersion` option in the configuration file.
