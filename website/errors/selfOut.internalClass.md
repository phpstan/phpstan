---
title: "selfOut.internalClass"
shortDescription: "Tag @phpstan-self-out references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalType {}
}

namespace App {
	class Builder {
		/**
		 * @phpstan-self-out \Vendor\InternalType
		 */
		public function build(): void {}
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of their package and may change or be removed at any time. Referencing them in a `@phpstan-self-out` tag creates a dependency on an internal implementation detail that may break without notice.

## How to fix it

Replace the internal class with a public type:

```diff-php
 class Builder {
 	/**
-	 * @phpstan-self-out \Vendor\InternalType
+	 * @phpstan-self-out \Vendor\PublicType
 	 */
 	public function build(): void {}
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
