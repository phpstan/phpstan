---
title: "traitUse.internalTrait"
shortDescription: "Trait use statement references an internal trait from another package."
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
}

namespace App {
	class MyClass {
		use \Vendor\InternalTrait;
	}
}
```

## Why is it reported?

The `use` statement inside a class body references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal trait from another package creates a dependency on implementation details that are not guaranteed to be stable.

## How to fix it

Use a public (non-internal) trait from the package instead:

```diff-php
 namespace App {
 	class MyClass {
-		use \Vendor\InternalTrait;
+		use \Vendor\PublicTrait;
 	}
 }
```

If the trait is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
