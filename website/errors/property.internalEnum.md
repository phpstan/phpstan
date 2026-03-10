---
title: "property.internalEnum"
shortDescription: "Property type declaration references an internal enum."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalStatus {
		case Active;
		case Inactive;
	}
}

namespace App {
	class Foo {
		public \Vendor\InternalStatus $status; // error: Property $status references internal enum Vendor\InternalStatus in its type.
	}
}
```

## Why is it reported?

A property's native type declaration references an enum that is marked as `@internal`. Internal enums are not part of the package's public API and may change or be removed without notice in future versions. Using an internal enum in a property type creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) enum or type provided by the library:

```diff-php
 namespace App {
 	class Foo {
-		public \Vendor\InternalStatus $status;
+		public \Vendor\PublicStatus $status;
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
