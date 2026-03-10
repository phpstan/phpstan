---
title: "staticMethod.internalTrait"
shortDescription: "Static method is called on an internal trait from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {
		public static function helper(): string {
			return 'result';
		}
	}
}

namespace App {
	function test(): void {
		\Vendor\InternalTrait::helper(); // error: Call to static method helper() of internal trait Vendor\InternalTrait from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static method is being called on a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without prior notice. Calling static methods on internal traits creates a dependency on implementation details that should not be relied upon.

## How to fix it

Use the public API of the package instead of calling static methods on internal traits:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\InternalTrait::helper();
+		\Vendor\PublicHelper::helper();
 	}
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
