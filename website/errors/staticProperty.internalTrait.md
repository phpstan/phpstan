---
title: "staticProperty.internalTrait"
shortDescription: "Static property is accessed on an internal trait from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {
		public static string $debug = 'off';
	}
}

namespace App {
	function test(): string {
		return \Vendor\InternalTrait::$debug; // error: Access to static property $debug of internal trait Vendor\InternalTrait from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static property is being accessed on a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice. Accessing static properties on internal traits creates a dependency on implementation details that should not be relied upon.

## How to fix it

Use the public API of the package instead of accessing static properties on internal traits:

```diff-php
 namespace App {
 	function test(): string {
-		return \Vendor\InternalTrait::$debug;
+		return \Vendor\PublicConfig::getDebugMode();
 	}
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
