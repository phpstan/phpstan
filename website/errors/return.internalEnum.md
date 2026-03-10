---
title: "return.internalEnum"
shortDescription: "Return type declaration references an internal enum from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalStatus {
		case Active;
	}
}

namespace App {
	function getStatus(): \Vendor\InternalStatus {
		return \Vendor\InternalStatus::Active;
	}
}
```

## Why is it reported?

The native return type declaration of a function or method references an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them. Exposing an internal enum in a return type creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal enum with a public enum or interface from the package in the return type:

```diff-php
 namespace App {
-	function getStatus(): \Vendor\InternalStatus {
-		return \Vendor\InternalStatus::Active;
+	function getStatus(): \Vendor\PublicStatus {
+		// ...
 	}
 }
```

If the enum is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
