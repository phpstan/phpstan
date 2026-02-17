---
title: "requireImplements.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum InternalEnum
{
	case A;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalEnum;

/**
 * @phpstan-require-implements InternalEnum
 */
trait MyTrait
{
}
```

## Why is it reported?

A `@phpstan-require-implements` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice.

## How to fix it

Use a public (non-internal) type in the `@phpstan-require-implements` tag instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
