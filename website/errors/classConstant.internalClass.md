---
title: "classConstant.internalClass"
shortDescription: "Accessing a constant on a class marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalConfig {
		public const VERSION = '1.0';
	}
}

namespace App {
	function test(): string {
		return \Vendor\InternalConfig::VERSION; // error: Access to constant VERSION of internal class Vendor\InternalConfig from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A constant is being accessed on a class that is marked as `@internal`. Internal classes are not meant to be used outside the package or namespace where they are defined. Accessing constants on internal classes creates a dependency on implementation details that may change without notice.

## How to fix it

Use a public (non-internal) API to access the information:

```diff-php
 namespace App {
 	function test(): string {
-		return \Vendor\InternalConfig::VERSION;
+		return \Vendor\Application::getVersion();
 	}
 }
```

If no public API exists, consider requesting one from the library maintainers. Relying on internal classes is fragile and may break on library updates.
