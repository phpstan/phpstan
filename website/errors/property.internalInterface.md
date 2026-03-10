---
title: "property.internalInterface"
shortDescription: "Property type declaration references an internal interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {
		public function execute(): void;
	}
}

namespace App {
	class Foo {
		public \Vendor\InternalInterface $service; // error: Property $service references internal interface Vendor\InternalInterface in its type.
	}
}
```

## Why is it reported?

A property's native type declaration references an interface that has been marked as `@internal`. Internal interfaces are implementation details of their package and are not meant to be used by external code. They may change or be removed without notice in future versions. Using an internal interface in a property type creates a fragile dependency on implementation details.

## How to fix it

Replace the internal interface with the package's public API:

```diff-php
 namespace App {
 	class Foo {
-		public \Vendor\InternalInterface $service;
+		public \Vendor\PublicInterface $service;
 	}
 }
```

If no public alternative exists, contact the library maintainers to request a public API for the functionality.
