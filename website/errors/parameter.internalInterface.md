---
title: "parameter.internalInterface"
shortDescription: "Parameter type declaration uses an internal interface from another package."
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
	function process(\Vendor\InternalInterface $handler): void {}
}
```

## Why is it reported?

A function or method parameter uses an interface marked with the `@internal` tag from another namespace as its type declaration. Internal interfaces are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice. Using an internal interface as a parameter type creates a dependency on an unstable API.

## How to fix it

Replace the internal interface with a public API type:

```diff-php
 namespace App {
-	function process(\Vendor\InternalInterface $handler): void {}
+	function process(\Vendor\PublicInterface $handler): void {}
 }
```
