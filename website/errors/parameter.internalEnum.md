---
title: "parameter.internalEnum"
shortDescription: "Parameter type declaration uses an internal enum from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalStatus: string {
		case Active = 'active';
	}
}

namespace App {
	function process(\Vendor\InternalStatus $status): void {}
}
```

## Why is it reported?

The parameter type hint references an enum that is marked as `@internal`. Internal enums are not part of the public API and are intended for use only within the package or namespace where they are defined. Using an internal enum as a parameter type outside its root namespace means the code depends on an implementation detail that may change or be removed without notice.

## How to fix it

Use the public API provided by the package instead of referencing internal enums directly:

```diff-php
 namespace App {
-	function process(\Vendor\InternalStatus $status): void {}
+	function process(\Vendor\PublicStatus $status): void {}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
