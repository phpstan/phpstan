---
title: "function.internal"
shortDescription: "Called function is marked as internal and not part of the public API."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	function internalHelper(): void {}

	function publicHelper(): void {}
}

namespace App {
	function test(): void {
		\Vendor\internalHelper(); // error: Call to internal function Vendor\internalHelper() from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

The function being called is marked with the `@internal` PHPDoc tag. Internal functions are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Calling an internal function from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice in future versions.

## How to fix it

Use a public API function provided by the library instead:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\internalHelper();
+		\Vendor\publicHelper();
 	}
 }
```

If no public alternative exists, contact the library maintainer to request a public API for the functionality, or implement the needed functionality directly in the application code.
