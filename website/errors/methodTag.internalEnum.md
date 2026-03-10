---
title: "methodTag.internalEnum"
shortDescription: "PHPDoc @method tag references an internal enum."
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
	/**
	 * @method \Vendor\InternalStatus getStatus()
	 */
	class MyClass {}
}
```

## Why is it reported?

A `@method` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in `@method` tags creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@method` tag instead:

```diff-php
 namespace App {
 	/**
-	 * @method \Vendor\InternalStatus getStatus()
+	 * @method \Vendor\PublicStatus getStatus()
 	 */
 	class MyClass {}
 }
```

If the library provides a public alternative, use that. Otherwise, define your own type that serves the same purpose.
