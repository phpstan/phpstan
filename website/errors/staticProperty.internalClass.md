---
title: "staticProperty.internalClass"
shortDescription: "Static property is accessed on an internal class from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalConfig {
		public static bool $debug = false;
	}
}

namespace App {
	function test(): bool {
		return \Vendor\InternalConfig::$debug; // error: Access to static property $debug of internal class Vendor\InternalConfig from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static property is being accessed on a class that is marked as `@internal`. Internal classes are not meant to be used outside of their own package. Depending on their static properties creates a coupling to implementation details that can change at any time without following semantic versioning.

## How to fix it

Use the public API provided by the package instead of accessing internal class properties directly:

```diff-php
 namespace App {
 	function test(): bool {
-		return \Vendor\InternalConfig::$debug;
+		return \Vendor\PublicConfig::isDebug();
 	}
 }
```
