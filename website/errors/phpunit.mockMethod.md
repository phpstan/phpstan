---
title: "phpunit.mockMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class UserService
{
	public function getName(): string
	{
		return 'John';
	}
}

class UserServiceTest extends TestCase
{
	public function testUser(): void
	{
		$mock = $this->createMock(UserService::class);
		$mock->method('getNamme')->willReturn('Jane');
	}
}
```

## Why is it reported?

The `method()` call on a PHPUnit mock object references a method that does not exist on the mocked class. This is most likely a typo in the method name. In the example above, `getNamme` should be `getName`.

Since mock objects do not validate method names at compile time, this kind of mistake would only be caught at runtime when the test is executed. PHPStan detects it statically.

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## How to fix it

Correct the method name to match an existing method on the mocked class:

```diff-php
 $mock = $this->createMock(UserService::class);
-$mock->method('getNamme')->willReturn('Jane');
+$mock->method('getName')->willReturn('Jane');
```
