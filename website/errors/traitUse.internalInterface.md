---
title: "traitUse.internalInterface"
shortDescription: "Trait use statement references an internal interface from another package."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	class MyClass {
		use \Vendor\InternalInterface;
	}
}
```

## Why is it reported?

The `use` statement inside a class body references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an interface in a `use` statement is already incorrect (only traits can be used this way), but the internal access error is also reported because the referenced symbol is internal to another package.

## How to fix it

Only traits can be used in a class body `use` statement. If you need functionality from the library, look for a public trait that provides it:

```diff-php
 namespace App {
 	class MyClass {
-		use \Vendor\InternalInterface;
+		use \Vendor\PublicTrait;
 	}
 }
```

If the interface should be implemented rather than used as a trait, use `implements` instead:

```diff-php
 namespace App {
-	class MyClass {
-		use \Vendor\InternalInterface;
+	class MyClass implements \Vendor\PublicInterface {
 	}
 }
```

If the interface is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
