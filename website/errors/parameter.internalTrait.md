---
title: "parameter.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
trait Loggable
{
	public function log(string $message): void
	{
		// ...
	}
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Loggable;

function doFoo(Loggable $value): void
{
}
```

## Why is it reported?

A parameter type declaration references a trait that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in parameter declarations creates a fragile dependency on implementation details that can change without notice.

Additionally, traits should generally not be used as types in parameter declarations, since PHP does not support using traits as type hints.

## How to fix it

Use a public (non-internal) interface or class as the parameter type instead:

```diff-php
-function doFoo(Loggable $value): void
+function doFoo(LoggableInterface $value): void
 {
 }
```
