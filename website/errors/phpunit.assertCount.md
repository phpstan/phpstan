---
title: "phpunit.assertCount"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	public function testItems(): void
	{
		$items = [1, 2, 3];
		$this->assertSame(3, count($items));
	}
}
```

## Why is it reported?

This rule is part of [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit).

Using `assertSame()` with `count()` to verify the number of elements in an array or countable object is less readable and produces worse failure messages than the dedicated `assertCount()` method. PHPUnit's `assertCount()` is specifically designed for this purpose and provides clearer output when the assertion fails.

This is also reported for `assertSame($expected, $variable->count())` on `Countable` objects.

## How to fix it

Replace `assertSame()` with `count()` by using `assertCount()`:

```diff-php
 public function testItems(): void
 {
 	$items = [1, 2, 3];
-	$this->assertSame(3, count($items));
+	$this->assertCount(3, $items);
 }
```

For `Countable` objects:

```diff-php
-$this->assertSame(3, $collection->count());
+$this->assertCount(3, $collection);
```
