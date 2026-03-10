---
title: "mixin.internalClass"
shortDescription: "PHPDoc @mixin tag references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalMixin {
		public function doFoo(): void {}
	}
}

namespace App {
	/** @mixin \Vendor\InternalMixin */
	class MyClass {}
}
```

## Why is it reported?

The `@mixin` PHPDoc tag references a class that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal classes in `@mixin` declarations creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) class in the `@mixin` tag instead:

```diff-php
 namespace App {
-	/** @mixin \Vendor\InternalMixin */
+	/** @mixin \Vendor\PublicMixin */
 	class MyClass {}
 }
```

If you need the functionality provided by the internal class, check whether the library provides a public API, or implement the methods directly in your class.
