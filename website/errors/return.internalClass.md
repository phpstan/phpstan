---
title: "return.internalClass"
shortDescription: "Return type declaration references an internal class from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalResult {}
}

namespace App {
	function getResult(): \Vendor\InternalResult {
		return new \Vendor\InternalResult();
	}
}
```

## Why is it reported?

The native return type declaration of a function or method references a class that is marked as `@internal`. Internal classes are not part of the public API of the package that defines them. Using an internal class in a return type exposes an implementation detail to callers, creating a dependency on something that may change or be removed without notice.

## How to fix it

Replace the internal class with a public class or interface from the package in the return type:

```diff-php
 namespace App {
-	function getResult(): \Vendor\InternalResult {
-		return new \Vendor\InternalResult();
+	function getResult(): \Vendor\ResultInterface {
+		// ...
 	}
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
