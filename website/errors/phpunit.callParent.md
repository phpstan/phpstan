---
title: "phpunit.callParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
	protected function setUp(): void
	{
		// important initialization
	}
}

class MyTest extends BaseTestCase
{
	protected function setUp(): void
	{
		$this->value = true; // Missing call to parent::setUp()
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

When a PHPUnit test class overrides `setUp()` or `tearDown()` and the parent class (other than `TestCase` itself) also defines these methods, failing to call the parent method can skip important initialization or cleanup logic. This can lead to subtle test failures or resource leaks.

## How to fix it

Add a call to the parent method:

```diff-php
 <?php declare(strict_types = 1);

 class MyTest extends BaseTestCase
 {
 	protected function setUp(): void
 	{
+		parent::setUp();
+
 		$this->value = true;
 	}
 }
```
