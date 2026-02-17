---
title: "classConstant.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
trait InternalTrait
{
	public const INTERNAL_VALUE = 42;
}

class PublicClass
{
	use InternalTrait;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

use SomeLibrary\PublicClass;

echo PublicClass::INTERNAL_VALUE;
```

## Why is it reported?

The constant is defined on a trait that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Accessing constants on internal traits from outside their package creates a fragile dependency.

## How to fix it

Use a public (non-internal) constant or API instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
