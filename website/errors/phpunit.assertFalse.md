---
title: "phpunit.assertFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	public function testSomething(): void
	{
		$this->assertSame(false, $this->getValue());
	}

	private function getValue(): bool
	{
		return false;
	}
}
```

## Why is it reported?

When `assertSame()` is called with the literal `false` as the expected value, it is clearer and more idiomatic to use `assertFalse()` instead. The `assertFalse()` method is specifically designed for this purpose and produces better failure messages.

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## How to fix it

Replace `assertSame(false, ...)` with `assertFalse(...)`:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 class MyTest extends TestCase
 {
 	public function testSomething(): void
 	{
-		$this->assertSame(false, $this->getValue());
+		$this->assertFalse($this->getValue());
 	}
 }
```
