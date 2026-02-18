---
title: "phpunit.coversMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Calculator::nonExistentMethod
 */
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

The `@covers` annotation references a method that does not exist on the specified class. This means the code coverage report will not correctly attribute coverage, and the annotation may indicate a typo or an outdated reference to a method that has been renamed or removed.

## How to fix it

Fix the method name in the `@covers` annotation to reference an existing method:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @covers \App\Calculator::nonExistentMethod
+ * @covers \App\Calculator::add
  */
 class CalculatorTest extends TestCase
 {
 	public function testAdd(): void
 	{
 		$calc = new \App\Calculator();
 		$this->assertSame(3, $calc->add(1, 2));
 	}
 }
```

If the `@covers` annotation references a class or function that cannot be found, make sure to use a fully qualified name (starting with `\`).
