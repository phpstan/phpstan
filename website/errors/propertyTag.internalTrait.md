---
title: "propertyTag.internalTrait"
shortDescription: "@property PHPDoc tag references an internal trait."
ignorable: true
unlikely: true
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
	/**
	 * @property \Vendor\InternalTrait $helper
	 */
	class MyClass {}
}
```

## Why is it reported?

The `@property` PHPDoc tag references a trait that has been marked as `@internal`. Internal traits are implementation details of a package or namespace and are not meant to be used by external code. They may change or be removed without notice in future versions.

Traits are not valid types in PHP, so using one as a type in a `@property` tag is problematic regardless of the internal status.

## How to fix it

Replace the internal trait reference in the `@property` tag with a valid, public type such as an interface or class:

```diff-php
 namespace App {
 	/**
-	 * @property \Vendor\InternalTrait $helper
+	 * @property \Vendor\PublicInterface $helper
 	 */
 	class MyClass {}
 }
```
