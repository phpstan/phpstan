---
title: "phpunit.dataProviderEmpty"
shortDescription: "Data provider method always returns an empty iterable, which is an error in PHPUnit 10 and newer."
ignorable: true
---

This error is reported by `phpstan/phpstan-phpunit`. The rule is currently enabled only with [Bleeding Edge](/blog/what-is-bleeding-edge).

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
	#[DataProvider('provideData')]
	public function testSomething(string $value): void
	{
		self::assertNotEmpty($value);
	}

	public static function provideData(): iterable
	{
		return [];
	}
}
```

## Why is it reported?

A data provider supplies the data sets a test method runs with. Every `return` statement in the method above returns an empty array or iterable, so the provider never supplies any data sets.

PHPUnit 9 skips a test whose data provider is empty. PHPUnit 10 and newer report it as an error. Either way the test never runs, so the code it was supposed to cover is not tested.

The rule reports the error only when the installed PHPUnit version is 10 or newer, or when the version cannot be detected. Generator-based providers (methods using `yield`) are not checked, because whether they yield anything depends on control flow.

## How to fix it

Add the data sets the test should run with:

```diff-php
 	public static function provideData(): iterable
 	{
-		return [];
+		return [
+			['foo'],
+			['bar'],
+		];
 	}
```

If the test no longer needs any data, remove the data provider and the test parameters, or remove the test itself:

```diff-php
 class FooTest extends TestCase
 {
-	#[DataProvider('provideData')]
-	public function testSomething(string $value): void
+	public function testSomething(): void
 	{
-		self::assertNotEmpty($value);
+		self::assertNotEmpty('foo');
 	}
-
-	public static function provideData(): iterable
-	{
-		return [];
-	}
 }
```
