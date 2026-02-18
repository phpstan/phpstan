---
title: "staticProperty.internalEnum"
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

	public static string $label = 'Status';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalStatus;

echo InternalStatus::$label;
```

## Why is it reported?

A static property is being accessed on an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them and may change or be removed without notice. Accessing static properties on internal enums creates a fragile dependency on implementation details.

This error can be triggered by accessing the enum class itself (which is internal), or by accessing an internal static property on an enum.

## How to fix it

Use the public API of the package instead of accessing static properties on internal enums:

```diff-php
 namespace App;

-use SomeLibrary\InternalStatus;
+use SomeLibrary\Status;

-echo InternalStatus::$label;
+echo Status::$label;
```

If the enum is internal to your own project and the usage is within the same root namespace, the error will not be reported.
