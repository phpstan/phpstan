---
title: "instanceof.internalInterface"
shortDescription: "Instanceof check references an internal interface from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	function checkHandler(object $obj): void
	{
		if ($obj instanceof \Vendor\InternalInterface) {
		}
	}
}
```

## Why is it reported?

The interface used in the `instanceof` expression has been marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. Relying on internal types in `instanceof` checks creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public interface or class provided by the package instead:

```diff-php
 function checkHandler(object $obj): void
 {
-	if ($obj instanceof \Vendor\InternalInterface) {
+	if ($obj instanceof \Vendor\PublicInterface) {
 	}
 }
```

If no public alternative exists, contact the package maintainer to request a public API.
