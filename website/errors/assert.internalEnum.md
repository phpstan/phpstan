---
title: "assert.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum Status: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Status;

class Validator
{
	/**
	 * @phpstan-assert Status $value
	 */
	public function assertStatus(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your assertions creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead. If you need to assert a specific type, check whether the library provides a public alternative, or define your own type that serves the same purpose.
