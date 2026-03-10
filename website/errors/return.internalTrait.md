---
title: "return.internalTrait"
shortDescription: "Return type declaration references an internal trait from another package."
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
	/** @return \Vendor\InternalTrait */
	function createObject() {
		return new \stdClass();
	}
}
```

## Why is it reported?

The return type of the function or method references a trait that is marked as `@internal`. Internal symbols are not meant to be used outside the package or namespace that defines them. Referencing an internal trait in a return type exposes an internal implementation detail in a public API, creating a dependency on something that may change without notice.

Traits should generally not be used as return types, since PHP does not support using traits as type hints.

## How to fix it

Replace the internal trait with a public interface or class in the return type:

```diff-php
 namespace App {
-	/** @return \Vendor\InternalTrait */
+	/** @return \Vendor\PublicInterface */
 	function createObject() {
 		return new \stdClass();
 	}
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
