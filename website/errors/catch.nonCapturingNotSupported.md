---
title: "catch.nonCapturingNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class HelloWorld
{
	public function hello(): void
	{
		try {
			throw new \Exception('Hello');
		} catch (\Exception) {
			echo 'An error occurred';
		}
	}
}
```

## Why is it reported?

Non-capturing catches were introduced in PHP 8.0. This syntax allows catching an exception without assigning it to a variable (e.g., `catch (\Exception)` instead of `catch (\Exception $e)`). When PHPStan is configured to analyse code for a PHP version earlier than 8.0, this syntax is not valid and will cause a syntax error at runtime.

## How to fix it

Assign the caught exception to a variable:

```diff-php
 <?php declare(strict_types = 1);

 class HelloWorld
 {
 	public function hello(): void
 	{
 		try {
 			throw new \Exception('Hello');
-		} catch (\Exception) {
+		} catch (\Exception $e) {
 			echo 'An error occurred';
 		}
 	}
 }
```

Or configure PHPStan to analyse the code for PHP 8.0 or later by setting the `phpVersion` option in the configuration file.
