---
title: "selfOut.internalTrait"
shortDescription: "Tag @phpstan-self-out references an internal trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {}
}

namespace App {
	class Builder {
		/**
		 * @phpstan-self-out \Vendor\InternalTrait
		 */
		public function build(): void {}
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not meant to be used outside the package that defines them.

Note: triggering this identifier requires using a trait in `@phpstan-self-out`, which is not a valid type. PHPStan therefore always also reports a `selfOut.trait` error alongside this one.

## How to fix it

Replace the internal trait with a public interface or class:

```diff-php
 class Builder {
 	/**
-	 * @phpstan-self-out \Vendor\InternalTrait
+	 * @phpstan-self-out \Vendor\PublicInterface
 	 */
 	public function build(): void {}
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
