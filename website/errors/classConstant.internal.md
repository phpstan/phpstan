---
title: "classConstant.internal"
shortDescription: "Accessing a class constant marked as @internal from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	class Config {
		/** @internal */
		public const DEBUG_MODE = true;

		public const VERSION = '1.0';
	}
}

namespace App {
	function test(): bool {
		return \Vendor\Config::DEBUG_MODE; // error: Access to internal constant Vendor\Config::DEBUG_MODE from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

The class constant is marked as `@internal` and is being accessed from outside the package where it is defined. Internal constants are not part of the package's public API and may change or be removed without notice.

## How to fix it

Use a public (non-internal) constant or API instead:

```diff-php
 namespace App {
 	function test(): string {
-		return \Vendor\Config::DEBUG_MODE;
+		return \Vendor\Config::VERSION;
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
