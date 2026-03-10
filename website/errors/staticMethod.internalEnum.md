---
title: "staticMethod.internalEnum"
shortDescription: "Static method is called on an internal enum from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalStatus: string {
		case Active = 'active';

		public static function default(): self {
			return self::Active;
		}
	}
}

namespace App {
	function test(): void {
		\Vendor\InternalStatus::default(); // error: Call to static method default() of internal enum Vendor\InternalStatus from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static method is being called on an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them and may change or be removed without notice. Calling static methods on them creates a dependency on implementation details.

## How to fix it

Use the public API of the package instead of calling static methods on internal enums:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\InternalStatus::default();
+		\Vendor\PublicStatus::default();
 	}
 }
```

If the enum is internal to your own project and the usage is within the same root namespace, the error will not be reported.
