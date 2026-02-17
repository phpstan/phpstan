---
title: "method.unused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class UserService
{
	public function getUser(int $id): User
	{
		return $this->findUser($id);
	}

	private function formatName(string $name): string
	{
		return ucfirst($name);
	}

	private function findUser(int $id): User
	{
		// ...
	}
}
```

## Why is it reported?

A private method is declared but never called anywhere within the class. Since private methods cannot be accessed from outside the class or from child classes, an unused private method is dead code that can be safely removed.

## How to fix it

Remove the unused private method, or start using it if it was intended to be called.

```diff-php
 <?php declare(strict_types = 1);

 class UserService
 {
 	public function getUser(int $id): User
 	{
 		return $this->findUser($id);
 	}

-	private function formatName(string $name): string
-	{
-		return ucfirst($name);
-	}
-
 	private function findUser(int $id): User
 	{
 		// ...
 	}
 }
```
