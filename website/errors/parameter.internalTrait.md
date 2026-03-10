---
title: "parameter.internalTrait"
shortDescription: "Parameter type declaration references an internal trait from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {
		public function doSomething(): void {}
	}

	class Foo {
		use InternalTrait;
	}
}

namespace App {
	/** @param \Vendor\InternalTrait $x */
	function process($x): void {}
}
```

## Why is it reported?

A parameter type declaration references a trait that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in parameter declarations creates a fragile dependency on implementation details that can change without notice.

Traits should generally not be used as types in parameter declarations, since PHP does not support using traits as type hints.

## How to fix it

Use a public (non-internal) interface or class as the parameter type instead:

```diff-php
 namespace App {
-	/** @param \Vendor\InternalTrait $x */
+	/** @param \Vendor\PublicInterface $x */
 	function process($x): void {}
 }
```
