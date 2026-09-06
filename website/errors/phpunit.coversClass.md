---
title: "phpunit.coversClass"
shortDescription: "#[CoversClass] attribute references a class that does not exist."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\NonExistentService::class)]
class MyTest extends TestCase
{
	public function testSomething(): void
	{
		$this->assertTrue(true);
	}
}
```

## Why is it reported?

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

The `#[CoversClass]` attribute on the test class references a class that does not exist. This attribute is used by PHPUnit to track code coverage. When the referenced class cannot be found, the coverage data will be incorrect and PHPUnit may produce warnings or errors when generating coverage reports.

This typically happens when the class has been renamed, moved to a different namespace, or deleted without updating the test attributes.

## How to fix it

Update the attribute to reference the correct class:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\Attributes\CoversClass;
 use PHPUnit\Framework\TestCase;

-#[CoversClass(\App\NonExistentService::class)]
+#[CoversClass(\App\UserService::class)]
 class MyTest extends TestCase
 {
 	public function testSomething(): void
 	{
 		$this->assertTrue(true);
 	}
 }
```
