---
title: "property.internalClass"
shortDescription: "Property type declaration references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalType {}
}

namespace App {
	class Foo {
		public \Vendor\InternalType $prop; // error: Property $prop references internal class Vendor\InternalType in its type.
	}
}
```

## Why is it reported?

A property's native type declaration references a class that is marked as `@internal`. Internal classes are not part of the package's public API and may change or be removed without notice in future versions. Using an internal class in a property type creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) type provided by the library instead:

```diff-php
 namespace App {
 	class Foo {
-		public \Vendor\InternalType $prop;
+		public \Vendor\PublicType $prop;
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
