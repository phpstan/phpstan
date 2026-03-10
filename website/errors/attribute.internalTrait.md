---
title: "attribute.internalTrait"
shortDescription: "Attribute references an internal trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {}

	class Foo {
		use InternalTrait;
	}
}

namespace App {
	use Vendor\InternalTrait;

	#[InternalTrait]
	class Bar {}
}
```

## Why is it reported?

An attribute references a trait that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Using internal types in attribute contexts creates a fragile dependency on implementation details.

Traits cannot be used as attribute classes, so using a trait in an attribute context is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) attribute class instead:

```diff-php
 namespace App;

-use Vendor\InternalTrait;
+use Vendor\PublicAttribute;

-#[InternalTrait]
+#[PublicAttribute]
 class Bar {}
```

If no public alternative exists, define your own attribute class.
