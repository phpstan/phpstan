---
title: "staticMethod.internalInterface"
shortDescription: "Static method is called on an internal interface from outside its package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface Factory {
		public static function create(): static;
	}
}

namespace App {
	function test(): void {
		\Vendor\Factory::create(); // error: Call to static method create() of internal interface Vendor\Factory from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A static method is being called on an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without prior notice.

## How to fix it

Use the public API of the package instead of calling static methods on internal interfaces:

```diff-php
 namespace App {
 	function test(): void {
-		\Vendor\Factory::create();
+		\Vendor\PublicFactory::create();
 	}
 }
```

If the interface is internal to your own project and you are calling it from the same root namespace, the error will not be reported.
