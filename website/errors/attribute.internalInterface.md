---
title: "attribute.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
interface InternalInterface
{
}
```

```php
<?php declare(strict_types = 1);

// In your code, referencing the internal interface in an attribute context:
// Attribute references internal interface SomeLibrary\InternalInterface.
```

## Why is it reported?

An attribute references an interface that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Using internal types in attribute contexts creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) interface instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
