---
title: "assert.internalClass"
shortDescription: "Assertion in @phpstan-assert references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalHelper {}
}

namespace App {
	class Checker {
		/**
		 * @phpstan-assert \Vendor\InternalHelper $value
		 * @param mixed $value
		 */
		public function assertHelper($value): void
		{
		}
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references a class that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions. Depending on internal types in your assertions creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead:

```diff-php
 class Checker
 {
 	/**
-	 * @phpstan-assert \Vendor\InternalHelper $value
+	 * @phpstan-assert \Vendor\PublicHelper $value
 	 * @param mixed $value
 	 */
 	public function assertHelper($value): void
 	{
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
