---
title: "phpunit.coversInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

interface LoggerInterface
{
	public function log(string $message): void;
}

/**
 * @covers \LoggerInterface
 */
class LoggerTest extends TestCase
{
	public function testLog(): void
	{
		// ...
	}
}
```

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## Why is it reported?

The `@covers` annotation references an interface. PHPUnit code coverage tracks the execution of concrete code, and interfaces do not contain executable code. Covering an interface is not meaningful because there is no code to measure coverage for.

## How to fix it

Change the `@covers` annotation to reference the concrete class that implements the interface:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 /**
- * @covers \LoggerInterface
+ * @covers \FileLogger
  */
 class LoggerTest extends TestCase
 {
 	public function testLog(): void
 	{
 		// ...
 	}
 }
```
