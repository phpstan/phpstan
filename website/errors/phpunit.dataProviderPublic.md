---
title: "phpunit.dataProviderPublic"
shortDescription: "Data provider method is not public."
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
	public function testFoo(int $value): void
	{
		$this->assertGreaterThan(0, $value);
	}

	private function provideData(): array
	{
		return [[1], [2], [3]];
	}
}
```

## Why is it reported?

This rule is part of [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit).

A `#[DataProvider]` attribute references a method that is not public. PHPUnit requires data provider methods to be public so that the test runner can access them.

## How to fix it

Change the data provider method's visibility to `public`:

```diff-php
-	private function provideData(): array
+	public function provideData(): array
 	{
 		return [[1], [2], [3]];
 	}
```
