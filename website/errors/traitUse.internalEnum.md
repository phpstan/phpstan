---
title: "traitUse.internalEnum"
shortDescription: "Trait use statement references an internal enum from another package."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalEnum {
		case A;
	}
}

namespace App {
	class MyClass {
		use \Vendor\InternalEnum;
	}
}
```

## Why is it reported?

The `use` statement inside a class body references an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an enum in a `use` statement is already incorrect (only traits can be used this way), but the internal access error is also reported because the referenced symbol is internal to another package.

## How to fix it

Only traits can be used in a class body `use` statement. If you need functionality from the library, look for a public trait that provides it:

```diff-php
 namespace App {
 	class MyClass {
-		use \Vendor\InternalEnum;
+		use \Vendor\PublicTrait;
 	}
 }
```

If the enum is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
