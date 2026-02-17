---
title: "attribute.internalTrait"
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
}
```

```php
<?php declare(strict_types = 1);

// In your code, referencing the internal trait in an attribute context:
// Attribute references internal trait SomeLibrary\InternalTrait.
```

## Why is it reported?

An attribute references a trait that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Using internal types in attribute contexts creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) type instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
