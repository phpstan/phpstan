---
title: "varTag.internalClass"
shortDescription: "@var PHPDoc tag references an internal class from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalClass {}
}

namespace App {
	function getHelper(): object {
		return new \stdClass();
	}

	/** @var \Vendor\InternalClass $helper */
	$helper = getHelper();
}
```

## Why is it reported?

The `@var` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal class from another package in a `@var` tag creates a dependency on an implementation detail that is not guaranteed to be stable.

## How to fix it

Use a public class or interface from the package instead:

```diff-php
 namespace App {
-	/** @var \Vendor\InternalClass $helper */
+	/** @var \Vendor\PublicClass $helper */
 	$helper = getHelper();
 }
```

If the class is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
