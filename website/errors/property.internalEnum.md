---
title: "property.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum InternalStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalStatus;

function getStatusValue(InternalStatus $status): string
{
	return $status->value; // ERROR: Access to property $value of internal enum SomeLibrary\InternalStatus.
}
```

## Why is it reported?

The code accesses a property on an enum that is marked as `@internal`. Internal enums are not part of the package's public API and may change or be removed without notice in future versions. Accessing properties on internal enums creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) enum or the public API provided by the library:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalStatus;
+use SomeLibrary\Status;

-function getStatusValue(InternalStatus $status): string
+function getStatusValue(Status $status): string
 {
 	return $status->value;
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
