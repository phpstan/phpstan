---
title: "phpunit.coversMethod"
shortDescription: "#[CoversMethod] attribute references a method that does not exist on the class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(\App\Calculator::class, 'nonExistentMethod')]
class CalculatorTest extends TestCase
{
	public function testAdd(): void
	{
		$calc = new \App\Calculator();
		$this->assertSame(3, $calc->add(1, 2));
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

The `#[CoversMethod]` attribute references a method that does not exist on the specified class. This means the code coverage report will not correctly attribute coverage, and the attribute may indicate a typo or an outdated reference to a method that has been renamed or removed.

## How to fix it

Fix the method name in the `#[CoversMethod]` attribute to reference an existing method:

```diff-php
 <?php declare(strict_types = 1);

-#[CoversMethod(\App\Calculator::class, 'nonExistentMethod')]
+#[CoversMethod(\App\Calculator::class, 'add')]
 class CalculatorTest extends TestCase
 {
 	public function testAdd(): void
 	{
 		$calc = new \App\Calculator();
 		$this->assertSame(3, $calc->add(1, 2));
 	}
 }
```

If covering the entire class instead of a specific method, use `#[CoversClass]` instead:

```diff-php
-use PHPUnit\Framework\Attributes\CoversMethod;
+use PHPUnit\Framework\Attributes\CoversClass;

-#[CoversMethod(\App\Calculator::class, 'nonExistentMethod')]
+#[CoversClass(\App\Calculator::class)]
 class CalculatorTest extends TestCase
```
