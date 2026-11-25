---
title: "phpunit.callParent"
shortDescription: "Overridden setUp() or tearDown() does not call the parent method."
ignorable: true
autofixable: true
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

Running `vendor/bin/phpstan analyse --fix` adds the missing call automatically. `parent::setUp()` is inserted as the first statement of `setUp()`, and `parent::tearDown()` is inserted as the last statement of `tearDown()` (before a trailing `return`). The fix is not offered for methods declared in traits, or for a `tearDown()` that has an early `return` or a `throw`, because the added call would be skipped on that path.
