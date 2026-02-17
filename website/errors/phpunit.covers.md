---
title: "phpunit.covers"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @covers \App\NonExistentClass
 */
class MyTest extends \PHPUnit\Framework\TestCase
{
	public function testSomething(): void
	{
		// ...
	}
}
```

## Why is it reported?

The `@covers` or `@coversDefaultClass` annotation references an invalid class, function, or method. This can happen when:

- The referenced class or function does not exist
- The annotation value is empty
- The class name is not fully qualified (missing leading backslash)
- A `@coversDefaultClass` is used on a method instead of a class

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## How to fix it

Ensure the `@covers` annotation references an existing, fully qualified class or function:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @covers \App\NonExistentClass
+ * @covers \App\ExistingClass
  */
 class MyTest extends \PHPUnit\Framework\TestCase
 {
 	public function testSomething(): void
 	{
 		// ...
 	}
 }
```

If the class name is not fully qualified, add the leading backslash:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @covers MyClass
+ * @covers \App\MyClass
  */
 class MyTest extends \PHPUnit\Framework\TestCase
 {
 }
```
