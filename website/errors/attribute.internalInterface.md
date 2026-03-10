---
title: "attribute.internalInterface"
shortDescription: "Attribute references an internal interface."
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
	use Vendor\InternalInterface;

	#[InternalInterface]
	class Foo {}
}
```

## Why is it reported?

An attribute references an interface that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Using internal types in attribute contexts creates a fragile dependency on implementation details.

Interfaces cannot be used as attribute classes, so using an interface in an attribute context is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) attribute class instead:

```diff-php
 namespace App;

-use Vendor\InternalInterface;
+use Vendor\PublicAttribute;

-#[InternalInterface]
+#[PublicAttribute]
 class Foo {}
```

If no public alternative exists, define your own attribute class.
