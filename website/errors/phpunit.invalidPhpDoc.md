---
title: "phpunit.invalidPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @dataProvidergetData
	 */
	public function testSomething(int $value): void
	{
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

A PHPUnit annotation like `@dataProvider`, `@covers`, `@depends`, `@group`, or similar is missing a space between the annotation name and its value. PHPUnit requires these annotations to have a space separating the tag from its parameter. Without the space, PHPUnit will not recognise the annotation and will silently ignore it.

## How to fix it

Add a space between the annotation name and its value:

```diff-php
 /**
- * @dataProvidergetData
+ * @dataProvider getData
  */
 public function testSomething(int $value): void
 {
 }
```
