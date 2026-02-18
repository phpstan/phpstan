---
title: "staticMethod.internalEnum"
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

	public static function default(): self
	{
		return self::Active;
	}
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalStatus;

$status = InternalStatus::default();
```

## Why is it reported?

A static method is being called on an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them and may change or be removed without notice. Calling static methods on them creates a dependency on implementation details.

This error can be triggered by accessing the enum class itself (which is internal) or by calling an internal static method on an enum.

## How to fix it

Use the public API of the package instead of calling static methods on internal enums:

```diff-php
 namespace App;

-use SomeLibrary\InternalStatus;
+use SomeLibrary\Status;

-$status = InternalStatus::default();
+$status = Status::default();
```

If the enum is internal to your own project and the usage is within the same root namespace, the error will not be reported.
