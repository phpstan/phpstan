---
title: "staticMethod.internal"
shortDescription: "Called static method is marked as internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	class Service {
		/** @internal */
		public static function doInternal(): void {}

		public static function doPublic(): void {}
	}
}

namespace App {
	function test(): void {
		\Vendor\Service::doInternal(); // error: Call to internal static method Vendor\Service::doInternal() from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static method marked as `@internal` is being called from outside its root namespace. Internal methods are not part of the public API of the package and may change or be removed at any time without following semantic versioning. Calling them from external code creates a fragile dependency.

## How to fix it

Use the public API provided by the package instead:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\Service::doInternal();
+		\Vendor\Service::doPublic();
 	}
 }
```

If the method is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage (calls from outside the root namespace).
