---
title: "varTag.internalInterface"
shortDescription: "@var PHPDoc tag references an internal interface from another package."
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
	function getService(): object {
		return new \stdClass();
	}

	/** @var \Vendor\InternalInterface $service */
	$service = getService();
}
```

## Why is it reported?

The `@var` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal interface from another package in a `@var` tag creates a dependency on an implementation detail that is not guaranteed to be stable.

## How to fix it

Use a public (non-internal) interface or class from the package instead:

```diff-php
 namespace App {
-	/** @var \Vendor\InternalInterface $service */
+	/** @var \Vendor\PublicInterface $service */
 	$service = getService();
 }
```

If the interface is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
