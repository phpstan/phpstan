---
title: "argument.namedNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$this->doBar(i: 1);
	}

	public function doBar(int $i): void
	{
	}
}
```

## Why is it reported?

Named arguments were introduced in PHP 8.0. When PHPStan is configured to analyse code for a PHP version earlier than 8.0, using named arguments in function or method calls is not valid and will cause a fatal error at runtime.

In the example above, `i: 1` uses the named argument syntax to pass the value `1` to the parameter `$i`. This syntax is not available in PHP versions before 8.0.

## How to fix it

Use positional arguments instead of named arguments:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$this->doBar(i: 1);
+		$this->doBar(1);
 	}

 	public function doBar(int $i): void
 	{
 	}
 }
```

Or configure PHPStan to analyse the code for PHP 8.0 or later by setting the `phpVersion` option in the configuration file.
