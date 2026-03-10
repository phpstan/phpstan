---
title: "return.internalInterface"
shortDescription: "Return type declaration references an internal interface from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}

	class ConcreteClass implements InternalInterface {}
}

namespace App {
	function getHandler(): \Vendor\InternalInterface {
		return new \Vendor\ConcreteClass();
	}
}
```

## Why is it reported?

The return type of a function or method references an interface that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Exposing an internal interface in a return type creates a dependency on an implementation detail.

## How to fix it

Use a public (non-internal) type in the return type instead:

```diff-php
 namespace App {
-	function getHandler(): \Vendor\InternalInterface {
+	function getHandler(): \Vendor\PublicInterface {
 		return new \Vendor\ConcreteClass();
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
