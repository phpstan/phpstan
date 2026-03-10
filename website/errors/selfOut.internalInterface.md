---
title: "selfOut.internalInterface"
shortDescription: "Tag @phpstan-self-out references an internal interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	class Builder {
		/**
		 * @phpstan-self-out \Vendor\InternalInterface
		 */
		public function build(): void {}
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of their package and may change or be removed without notice. Using internal types in `@phpstan-self-out` annotations creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) interface in the `@phpstan-self-out` tag:

```diff-php
 class Builder {
 	/**
-	 * @phpstan-self-out \Vendor\InternalInterface
+	 * @phpstan-self-out \Vendor\PublicInterface
 	 */
 	public function build(): void {}
 }
```

If the interface is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
