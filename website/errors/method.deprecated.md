---
title: "method.deprecated"
ignorable: true
---

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## Code example

```php
<?php declare(strict_types = 1);

class UserRepository
{
	/** @deprecated Use findById() instead */
	public function getUser(int $id): object
	{
		return $this->findById($id);
	}

	public function findById(int $id): object
	{
		// ...
	}
}

$repo = new UserRepository();
$repo->getUser(1); // ERROR: Call to deprecated method getUser() of class UserRepository.
```

## Why is it reported?

A method marked with the `@deprecated` PHPDoc tag is being called. Deprecated methods are scheduled for removal in a future version and should no longer be used. The deprecation notice typically provides guidance on what to use instead.

## How to fix it

Replace the call with the recommended alternative:

```diff-php
 <?php declare(strict_types = 1);

 $repo = new UserRepository();
-$repo->getUser(1);
+$repo->findById(1);
```
