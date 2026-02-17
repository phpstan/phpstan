---
title: "selfOut.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum Status: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Status;

/**
 * @template T
 */
class Collection
{
	/**
	 * @phpstan-self-out self<Status>
	 */
	public function filterByStatus(): void
	{
		// ...
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them. Referencing an internal enum in a `@phpstan-self-out` tag creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Use a public (non-internal) type from the package in the `@phpstan-self-out` tag instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\Status;
+use SomeLibrary\PublicStatus;

 /**
  * @template T
  */
 class Collection
 {
 	/**
-	 * @phpstan-self-out self<Status>
+	 * @phpstan-self-out self<PublicStatus>
 	 */
 	public function filterByStatus(): void
 	{
 		// ...
 	}
 }
```

If the enum is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
