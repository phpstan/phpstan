---
title: "methodTag.internalEnum"
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

/**
 * @method Status getStatus()
 */
class Service
{
}
```

## Why is it reported?

A `@method` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your `@method` tags creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@method` tag instead. If the library provides a public alternative, use that. Otherwise, define your own type that serves the same purpose.
