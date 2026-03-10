---
title: "staticMethod.internalClass"
shortDescription: "Static method is called on an internal class from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalService {
		public static function doFoo(): void {}
	}
}

namespace App {
	function test(): void {
		\Vendor\InternalService::doFoo(); // error: Call to static method doFoo() of internal class Vendor\InternalService from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static method is being called on a class that is marked as `@internal`. Internal classes are not meant to be used outside of their own package, and calling methods on them creates a dependency on an implementation detail that can change at any time without following semantic versioning.

## How to fix it

Use the public API provided by the package instead of accessing internal classes directly:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\InternalService::doFoo();
+		\Vendor\PublicService::doFoo();
 	}
 }
```
