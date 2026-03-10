---
title: "traitUse.internalClass"
shortDescription: "Trait use statement references an internal class from another package."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalClass {}
}

namespace App {
	class MyClass {
		use \Vendor\InternalClass;
	}
}
```

## Why is it reported?

The `use` statement inside a class body references a class that is marked as `@internal`. Internal classes are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using a class in a `use` statement is already incorrect (only traits can be used this way), but the internal access error is also reported because the referenced symbol is internal to another package.

## How to fix it

Only traits can be used in a class body `use` statement. If you need functionality from the library, look for a public trait, interface, or class that provides it:

```diff-php
 namespace App {
 	class MyClass {
-		use \Vendor\InternalClass;
+		use \Vendor\PublicTrait;
 	}
 }
```

If the class is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
