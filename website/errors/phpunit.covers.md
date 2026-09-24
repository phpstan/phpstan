---
title: "phpunit.covers"
shortDescription: "Code coverage attribute references a non-existent class or function."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\App\NonExistentClass::class)]
class MyTest extends \PHPUnit\Framework\TestCase
{
	public function testSomething(): void
	{
		// ...
	}
}
```

## Why is it reported?

The `#[CoversClass]` attribute or `@covers` annotation references a class, function, or method that cannot be found. This can happen when:

- The referenced class or function does not exist
- The class name is not fully qualified

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## How to fix it

Ensure the `#[CoversClass]` attribute references an existing class:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\Attributes\CoversClass;

-#[CoversClass(\App\NonExistentClass::class)]
+#[CoversClass(\App\ExistingClass::class)]
 class MyTest extends \PHPUnit\Framework\TestCase
 {
 	public function testSomething(): void
 	{
 		// ...
 	}
 }
```
