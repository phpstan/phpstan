---
title: "phpunit.assertEquals"
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
		$expected = 5;
		$actual = 5;
		$this->assertEquals($expected, $actual);
	}
}
```

## Why is it reported?

This rule is part of [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit).

`assertEquals()` performs a loose comparison (`==`), while `assertSame()` performs a strict comparison (`===`). When both the expected and actual values are scalars of the same type, `assertSame()` should be used instead because it avoids unexpected type coercion and produces clearer failure messages.

## How to fix it

Replace `assertEquals()` with `assertSame()`:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 class MyTest extends TestCase
 {
 	public function testFoo(): void
 	{
 		$expected = 5;
 		$actual = 5;
-		$this->assertEquals($expected, $actual);
+		$this->assertSame($expected, $actual);
 	}
 }
```
