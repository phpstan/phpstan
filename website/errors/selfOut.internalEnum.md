---
title: "selfOut.internalEnum"
shortDescription: "Tag @phpstan-self-out references an internal enum."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

namespace Vendor {
	/** @internal */
	enum InternalStatus: string {
		case Active = 'active';
	}
}

namespace App {
	class Builder {
		/**
		 * @phpstan-self-out \Vendor\InternalStatus
		 */
		public function build(): void {}
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references an enum that is marked as `@internal`. Internal enums are not part of the public API of their package and may change or be removed without notice. Referencing an internal enum in a `@phpstan-self-out` tag creates a dependency on an implementation detail that may break at any time.

## How to fix it

Use a public (non-internal) type from the package in the `@phpstan-self-out` tag instead:

```diff-php
 class Builder {
 	/**
-	 * @phpstan-self-out \Vendor\InternalStatus
+	 * @phpstan-self-out \Vendor\PublicStatus
 	 */
 	public function build(): void {}
 }
```

If the enum is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
