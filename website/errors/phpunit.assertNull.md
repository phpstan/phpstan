---
title: "phpunit.assertNull"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	public function testFoo(): void
	{
		$value = null;
		$this->assertSame(null, $value);
	}
}
```

## Why is it reported?

This rule is part of [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit).

When asserting that a value is `null`, `assertNull()` should be used instead of `assertSame(null, $value)`. The dedicated method is more readable and provides clearer failure messages.

## How to fix it

Replace `assertSame(null, ...)` with `assertNull()`:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 class MyTest extends TestCase
 {
 	public function testFoo(): void
 	{
 		$value = null;
-		$this->assertSame(null, $value);
+		$this->assertNull($value);
 	}
 }
```
