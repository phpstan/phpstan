---
title: "phpunit.dataProviderStatic"
ignorable: true
---

This error is reported by `phpstan/phpstan-phpunit`.

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
	/**
	 * @dataProvider provideData
	 */
	public function testSomething(string $value): void
	{
		self::assertNotEmpty($value);
	}

	public function provideData(): iterable
	{
		yield ['bar'];
	}
}
```

## Why is it reported?

PHPUnit 10 and newer require data provider methods to be static. The referenced data provider method is not declared as `static`. Non-static data providers are deprecated since PHPUnit 10 and will cause errors in future versions.

## How to fix it

Add the `static` keyword to the data provider method:

```diff-php
 class FooTest extends TestCase
 {
 	/**
 	 * @dataProvider provideData
 	 */
 	public function testSomething(string $value): void
 	{
 		self::assertNotEmpty($value);
 	}

-	public function provideData(): iterable
+	public static function provideData(): iterable
 	{
 		yield ['bar'];
 	}
 }
```
