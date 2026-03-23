---
title: "phpunit.dataProviderMethod"
shortDescription: "Data provider references a method that does not exist on the test class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	#[DataProvider('provideData')]
	public function testSomething(string $value): void
	{
		self::assertNotEmpty($value);
	}

	public static function provideItems(): iterable
	{
		yield ['foo'];
	}
}
```

## Why is it reported?

The `#[DataProvider]` attribute or `@dataProvider` annotation references a method that does not exist on the test class. PHPUnit will fail at runtime when it cannot find the data provider method.

## How to fix it

Ensure the data provider method name matches exactly:

```diff-php
-#[DataProvider('provideData')]
+#[DataProvider('provideItems')]
 public function testSomething(string $value): void
 {
 	self::assertNotEmpty($value);
 }
```

Or create the missing method:

```diff-php
+public static function provideData(): iterable
+{
+	yield ['foo'];
+}
```
