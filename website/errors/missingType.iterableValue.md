---
title: "missingType.iterableValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class UserRepository
{
	/** @var array */
	private $users;

	/** @return array */
	public function getAll(): array
	{
		return $this->users;
	}
}
```

## Why is it reported?

An iterable type such as `array` is used without specifying the types of its values (and optionally keys). Without a value type, PHPStan cannot track what the iterable contains, reducing the effectiveness of type checking for any code that consumes the iterable.

This rule applies to properties, function/method return types, and function/method parameters. It is reported for any iterable type -- `array`, `iterable`, `Generator`, and other traversable types -- when the value type is missing.

## How to fix it

Specify the value type for the iterable using PHPDoc:

```diff-php
 <?php declare(strict_types = 1);

 class UserRepository
 {
-	/** @var array */
+	/** @var array<User> */
 	private $users;

-	/** @return array */
+	/** @return array<User> */
 	public function getAll(): array
 	{
 		return $this->users;
 	}
 }
```

If both keys and values matter, specify both:

```diff-php
 <?php declare(strict_types = 1);

 class UserRepository
 {
-	/** @var array */
+	/** @var array<int, User> */
 	private $users;
 }
```

For a more specific array shape, use array shape syntax:

```diff-php
 <?php declare(strict_types = 1);

-/** @return array */
+/** @return array{name: string, age: int} */
 function getConfig(): array
 {
 	return ['name' => 'app', 'age' => 1];
 }
```

In PHPStan 1.x this error was possible to ignore with configuration parameter `checkMissingIterableValueType: false` but in PHPStan 2.0 this parameter was removed in favor of ignoring by identifier:
