---
title: "phpunit.coversClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass App\NonExistentService
 */
class MyTest extends TestCase // ERROR: @coversDefaultClass references an invalid class App\NonExistentService.
{
	public function testSomething(): void
	{
		$this->assertTrue(true);
	}
}
```

## Why is it reported?

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

The `@coversDefaultClass` annotation on the test class references a class that does not exist. This annotation is used by PHPUnit to track code coverage. When the referenced class cannot be found, the coverage data will be incorrect and PHPUnit may produce warnings or errors when generating coverage reports.

This typically happens when the class has been renamed, moved to a different namespace, or deleted without updating the test annotations.

## How to fix it

Update the annotation to reference the correct class:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 /**
- * @coversDefaultClass App\NonExistentService
+ * @coversDefaultClass App\UserService
  */
 class MyTest extends TestCase
 {
 	public function testSomething(): void
 	{
 		$this->assertTrue(true);
 	}
 }
```

Make sure to use the fully qualified class name (including the namespace) in the `@coversDefaultClass` annotation.
