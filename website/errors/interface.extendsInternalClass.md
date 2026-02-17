---
title: "interface.extendsInternalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class InternalClass
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalClass;

interface MyInterface extends InternalClass
{
}
```

## Why is it reported?

An interface extends a type that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Depending on internal types in your interface definitions makes your code fragile.

## How to fix it

Use a public (non-internal) type instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
