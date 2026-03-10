---
title: "parameter.internalClass"
shortDescription: "Parameter type declaration uses an internal class from another package."
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
	function process(\Vendor\InternalType $param): void {}
}
```

## Why is it reported?

A function or method parameter uses a class marked with the `@internal` tag from another namespace as its type declaration. Internal classes are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice. Using an internal class as a parameter type creates a dependency on an unstable API.

## How to fix it

Replace the internal class with a public API type, such as an interface or a non-internal class:

```diff-php
 namespace App {
-	function process(\Vendor\InternalType $param): void {}
+	function process(\Vendor\PublicType $param): void {}
 }
```
