---
title: "mixin.internalEnum"
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
 * @mixin Status
 */
class StatusHelper
{
}
```

## Why is it reported?

A `@mixin` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your `@mixin` tags creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@mixin` tag instead. If the library provides a public alternative, use that. Otherwise, define your own type or remove the `@mixin` tag and implement the needed methods directly.
