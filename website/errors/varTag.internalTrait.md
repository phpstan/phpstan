---
title: "varTag.internalTrait"
shortDescription: "@var PHPDoc tag references an internal trait from another package."
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
	function getHelper(): object {
		return new \stdClass();
	}

	/** @var \Vendor\InternalTrait $obj */
	$obj = getHelper();
}
```

## Why is it reported?

The `@var` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal trait from another package in a `@var` tag creates a dependency on an implementation detail that is not guaranteed to be stable. Traits should generally not be used as types, since PHP does not support using traits as type hints.

## How to fix it

Use a public interface or class from the package instead of the internal trait:

```diff-php
 namespace App {
-	/** @var \Vendor\InternalTrait $obj */
+	/** @var \Vendor\PublicInterface $obj */
 	$obj = getHelper();
 }
```

If the trait is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
