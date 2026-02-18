---
title: "phpunit.coversDuplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\UserService
 */
class UserServiceTest extends TestCase
{
	/**
	 * @covers \App\UserService
	 */
	public function testCreate(): void
	{
		// ...
	}
}
```

## Why is it reported?

There are two cases where this identifier is reported:

1. A method-level `@covers` annotation references the same class or method that is already covered by a class-level `@covers` annotation. The method-level annotation is redundant because the class-level annotation already covers it.
2. The `@coversDefaultClass` annotation is defined multiple times on the same class.

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) package.

In the example above, both the class and the method have `@covers \App\UserService`, making the method-level annotation redundant.

## How to fix it

Remove the redundant method-level `@covers` annotation:

```diff-php
 /**
  * @covers \App\UserService
  */
 class UserServiceTest extends TestCase
 {
-	/**
-	 * @covers \App\UserService
-	 */
 	public function testCreate(): void
 	{
 		// ...
 	}
 }
```

If the method is intended to cover a specific method rather than the whole class, be more specific:

```diff-php
 	/**
-	 * @covers \App\UserService
+	 * @covers \App\UserService::create
 	 */
 	public function testCreate(): void
 	{
 		// ...
 	}
```
