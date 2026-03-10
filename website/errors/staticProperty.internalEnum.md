---
title: "staticProperty.internalEnum"
shortDescription: "Static property is accessed on an internal enum from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalStatus {
		case Active;

		public static string $label = 'Status';
	}
}

namespace App {
	function test(): string {
		return \Vendor\InternalStatus::$label; // error: Access to static property $label of internal enum Vendor\InternalStatus from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static property is being accessed on an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them and may change or be removed without notice. Accessing static properties on internal enums creates a fragile dependency on implementation details.

## How to fix it

Use the public API of the package instead of accessing static properties on internal enums:

```diff-php
 namespace App {
 	function test(): string {
-		return \Vendor\InternalStatus::$label;
+		return \Vendor\PublicStatus::$label;
 	}
 }
```

If the enum is internal to your own project and the usage is within the same root namespace, the error will not be reported.
