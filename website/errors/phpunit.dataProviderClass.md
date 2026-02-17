---
title: "phpunit.dataProviderClass"
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
	 * @dataProvider NonExistingClass::provideData
	 */
	public function testSomething(string $value): void
	{
		self::assertNotEmpty($value);
	}
}
```

## Why is it reported?

The `@dataProvider` annotation or `#[DataProvider]` attribute references a class that does not exist. PHPStan cannot resolve the class name, so the data provider method cannot be found.

In the example above, `NonExistingClass` is not a valid class, so the data provider `NonExistingClass::provideData` cannot be resolved.

## How to fix it

Use a valid, fully-qualified class name in the data provider reference:

```diff-php
 class FooTest extends TestCase
 {
 	/**
-	 * @dataProvider NonExistingClass::provideData
+	 * @dataProvider BarTest::provideData
 	 */
 	public function testSomething(string $value): void
 	{
 		self::assertNotEmpty($value);
 	}
 }
```

Or, if the data provider method is in the same class, remove the class prefix:

```diff-php
 class FooTest extends TestCase
 {
 	/**
-	 * @dataProvider NonExistingClass::provideData
+	 * @dataProvider provideData
 	 */
 	public function testSomething(string $value): void
 	{
 		self::assertNotEmpty($value);
 	}

 	public static function provideData(): iterable
 	{
 		yield ['value'];
 	}
 }
```
