---
title: "assert.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
trait Loggable
{
	public function log(string $message): void
	{
		// ...
	}
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Loggable;

class Checker
{
	/**
	 * @phpstan-assert Loggable $value
	 */
	public function assertLoggable(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references a trait that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your assertions creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead. If you need to assert against a trait's functionality, consider using a public interface that the trait implements, or define your own type.
