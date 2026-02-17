---
title: "class.missingImplements"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(): void;
}

/** @phpstan-require-implements Loggable */
trait LoggableTrait
{
	public function log(): void
	{
		// ...
	}
}

class UserService
{
	use LoggableTrait;
}
```

## Why is it reported?

The trait declares a `@phpstan-require-implements` tag that requires any class using the trait to implement a specific interface. The class using the trait does not implement the required interface.

In the example above, `LoggableTrait` requires the using class to implement `Loggable`, but `UserService` does not declare `implements Loggable`.

## How to fix it

Add the required interface to the class declaration:

```diff-php
 <?php declare(strict_types = 1);

-class UserService
+class UserService implements Loggable
 {
 	use LoggableTrait;
 }
```
