---
title: "propertyTag.internalEnum"
shortDescription: "@property PHPDoc tag references an internal enum."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalEnum {
		case A;
	}
}

namespace App {
	/**
	 * @property \Vendor\InternalEnum $status
	 */
	class MyClass {}
}
```

## Why is it reported?

A `@property` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice.

## How to fix it

Use a public (non-internal) type in the `@property` tag instead:

```diff-php
 namespace App {
 	/**
-	 * @property \Vendor\InternalEnum $status
+	 * @property \Vendor\PublicEnum $status
 	 */
 	class MyClass {}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
