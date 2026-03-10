---
title: "methodTag.internalClass"
shortDescription: "PHPDoc @method tag references an internal class."
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
	/**
	 * @method \Vendor\InternalResult doFoo()
	 */
	class MyClass {}
}
```

## Why is it reported?

A `@method` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of the package that defines them. Using internal types in `@method` tags creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public (non-internal) type from the package instead:

```diff-php
 namespace App {
 	/**
-	 * @method \Vendor\InternalResult doFoo()
+	 * @method \Vendor\PublicResult doFoo()
 	 */
 	class MyClass {}
 }
```

If no public alternative exists, contact the package maintainer to request a public API, or define a local interface in the application code.
