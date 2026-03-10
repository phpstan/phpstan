---
title: "methodTag.internalTrait"
shortDescription: "PHPDoc @method tag references an internal trait."
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
	 * @method \Vendor\InternalTrait getTrait()
	 */
	class MyClass {}
}
```

## Why is it reported?

A `@method` PHPDoc tag references a trait marked with the `@internal` tag from another package. Internal traits are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice.

Traits are not valid types in PHP, so using one in a `@method` tag is problematic regardless of the internal status.

## How to fix it

Replace the internal trait type reference with a public API type:

```diff-php
 namespace App {
 	/**
-	 * @method \Vendor\InternalTrait getTrait()
+	 * @method \Vendor\PublicInterface getTrait()
 	 */
 	class MyClass {}
 }
```
