---
title: "phpunit.assertTrue"
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
		$this->assertSame(true, $this->isValid());
	}
}
```

## Why is it reported?

When the expected value in `assertSame()` is the literal `true`, using `assertTrue()` is more idiomatic and produces clearer failure messages. PHPUnit provides dedicated assertion methods for boolean values.

## How to fix it

Replace `assertSame(true, ...)` with `assertTrue(...)`:

```diff-php
-$this->assertSame(true, $this->isValid());
+$this->assertTrue($this->isValid());
```
