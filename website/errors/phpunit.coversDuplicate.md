---
title: "phpunit.coversDuplicate"
shortDescription: "Duplicate #[CoversClass] attribute is redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\App\UserService::class)]
#[CoversClass(\App\UserService::class)]
class UserServiceTest extends TestCase
{
	public function testCreate(): void
	{
		// ...
	}
}
```

## Why is it reported?

The `#[CoversClass]` attribute referencing the same class appears more than once on the test class. The duplicate attribute is redundant and should be removed.

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) package.

## How to fix it

Remove the duplicate `#[CoversClass]` attribute:

```diff-php
 #[CoversClass(\App\UserService::class)]
-#[CoversClass(\App\UserService::class)]
 class UserServiceTest extends TestCase
 {
 	public function testCreate(): void
 	{
 		// ...
 	}
 }
```

If covering a specific method in addition to the whole class, use `#[CoversMethod]` instead:

```diff-php
+use PHPUnit\Framework\Attributes\CoversMethod;

 #[CoversClass(\App\UserService::class)]
-#[CoversClass(\App\UserService::class)]
+#[CoversMethod(\App\UserService::class, 'create')]
 class UserServiceTest extends TestCase
```
