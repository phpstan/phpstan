---
title: "assert.internalInterface"
shortDescription: "Assertion in @phpstan-assert references an internal interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface Logger {
		public function log(string $message): void;
	}
}

namespace App {
	class Checker {
		/**
		 * @phpstan-assert \Vendor\Logger $value
		 * @param mixed $value
		 */
		public function assertLogger($value): void
		{
		}
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references an interface that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your assertions creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@phpstan-assert` tag instead:

```diff-php
 class Checker
 {
 	/**
-	 * @phpstan-assert \Vendor\Logger $value
+	 * @phpstan-assert \Psr\Log\LoggerInterface $value
 	 * @param mixed $value
 	 */
 	public function assertLogger($value): void
 	{
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
