---
title: "phpunit.dataProviderClass"
shortDescription: "Data provider references a class that does not exist."
ignorable: true
---

This error is reported by `phpstan/phpstan-phpunit`.

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
	#[DataProviderExternal(NonExistingClass::class, 'provideData')]
	public function testSomething(string $value): void
	{
		self::assertNotEmpty($value);
	}
}
```

## Why is it reported?

The `#[DataProviderExternal]` attribute or `@dataProvider` annotation references a class that does not exist. PHPStan cannot resolve the class name, so the data provider method cannot be found.

In the example above, `NonExistingClass` is not a valid class, so the data provider `NonExistingClass::provideData` cannot be resolved.

## How to fix it

Use a valid, fully-qualified class name in the data provider reference:

```diff-php
 class FooTest extends TestCase
 {
-	#[DataProviderExternal(NonExistingClass::class, 'provideData')]
+	#[DataProviderExternal(BarTest::class, 'provideData')]
 	public function testSomething(string $value): void
 	{
 		self::assertNotEmpty($value);
 	}
 }
```

Or, if the data provider method is in the same class, use `#[DataProvider]` instead:

```diff-php
+use PHPUnit\Framework\Attributes\DataProvider;

 class FooTest extends TestCase
 {
-	#[DataProviderExternal(NonExistingClass::class, 'provideData')]
+	#[DataProvider('provideData')]
 	public function testSomething(string $value): void
 	{
 		self::assertNotEmpty($value);
 	}

+	public static function provideData(): iterable
+	{
+		yield ['value'];
+	}
 }
```
