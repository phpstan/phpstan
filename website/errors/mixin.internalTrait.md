---
title: "mixin.internalTrait"
shortDescription: "PHPDoc @mixin tag references an internal trait."
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
	/** @mixin \Vendor\InternalTrait */
	class MyClass {}
}
```

## Why is it reported?

The `@mixin` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. Referencing internal types in PHPDoc tags creates a dependency on implementation details that may change without notice in future versions of the package.

Traits are not valid types in PHP, so using one in a `@mixin` tag is problematic regardless of the internal status.

## How to fix it

Use a public class or interface provided by the package instead:

```diff-php
 namespace App {
-	/** @mixin \Vendor\InternalTrait */
+	/** @mixin \Vendor\PublicClass */
 	class MyClass {}
 }
```

If no public alternative exists, remove the `@mixin` tag and implement the needed methods directly in the class.
