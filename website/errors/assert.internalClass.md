---
title: "assert.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class InternalHelper
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalHelper;

class Checker
{
	/**
	 * @phpstan-assert InternalHelper $value
	 */
	public function assertHelper(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references a class that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions. Depending on internal types in your assertions creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead:

```diff-php
 <?php declare(strict_types = 1);

 class Checker
 {
 	/**
-	 * @phpstan-assert InternalHelper $value
+	 * @phpstan-assert PublicHelper $value
 	 */
 	public function assertHelper(mixed $value): void
 	{
 		// ...
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
