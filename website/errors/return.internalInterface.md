---
title: "return.internalInterface"
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

// In your code:

namespace App;

use SomeLibrary\InternalInterface;

function doFoo(): InternalInterface
{
	// ...
}
```

## Why is it reported?

The return type of a function or method references an interface that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice.

## How to fix it

Use a public (non-internal) type in the return type instead. If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
