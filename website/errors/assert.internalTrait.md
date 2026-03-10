---
title: "assert.internalTrait"
shortDescription: "Assertion in @phpstan-assert references an internal trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait Loggable {
		public function log(string $message): void {}
	}

	class Foo {
		use Loggable;
	}
}

namespace App {
	class Checker {
		/**
		 * @phpstan-assert \Vendor\Loggable $value
		 * @param mixed $value
		 */
		public function assertLoggable($value): void
		{
		}
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references a trait that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your assertions creates a fragile dependency on implementation details that can change without notice.

Traits cannot be used as types, so referencing a trait in a `@phpstan-assert` tag is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) class or interface in the `@phpstan-assert` tag instead:

```diff-php
 class Checker
 {
 	/**
-	 * @phpstan-assert \Vendor\Loggable $value
+	 * @phpstan-assert \Vendor\PublicInterface $value
 	 * @param mixed $value
 	 */
-	public function assertLoggable($value): void
+	public function assertPublicInterface($value): void
 	{
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
