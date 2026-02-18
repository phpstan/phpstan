---
title: "selfOut.internalInterface"
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

class Builder
{
	/**
	 * @phpstan-self-out InternalInterface
	 */
	public function configure(): void
	{
		// ...
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without prior notice. Using internal types in `@phpstan-self-out` annotations creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) interface in the `@phpstan-self-out` tag:

```diff-php
 class Builder
 {
 	/**
-	 * @phpstan-self-out InternalInterface
+	 * @phpstan-self-out PublicInterface
 	 */
 	public function configure(): void
 	{
 		// ...
 	}
 }
```

If the interface is internal to your own project and the usage is within the same root namespace, the error will not be reported.
