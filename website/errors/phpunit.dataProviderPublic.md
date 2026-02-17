---
title: "phpunit.dataProviderPublic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	/**
	 * @dataProvider provideData
	 */
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

A `@dataProvider` annotation references a method that is not public. PHPUnit requires data provider methods to be public so that the test runner can access them.

## How to fix it

Change the data provider method's visibility to `public`:

```diff-php
 <?php declare(strict_types = 1);

-	private function provideData(): array
+	public function provideData(): array
 	{
 		return [[1], [2], [3]];
 	}
```
