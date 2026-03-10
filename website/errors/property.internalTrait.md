---
title: "property.internalTrait"
shortDescription: "Property type declaration references an internal trait."
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
	class Foo {
		/** @var \Vendor\InternalTrait */
		public $helper; // error: Property $helper references internal trait Vendor\InternalTrait in its type.
	}
}
```

## Why is it reported?

A property type references a trait that has been marked as `@internal`. Internal traits are implementation details of their package and are not meant to be used by external code. They may change or be removed without notice in future versions. Referencing an internal trait in a property type declaration creates a fragile dependency on implementation details.

## How to fix it

Replace the internal trait reference with the package's public API:

```diff-php
 namespace App {
 	class Foo {
-		/** @var \Vendor\InternalTrait */
-		public $helper;
+		public \Vendor\PublicInterface $helper;
 	}
 }
```

If no public alternative exists, contact the library maintainers to request a public API for the functionality.
