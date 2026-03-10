---
title: "attribute.internalEnum"
shortDescription: "Attribute references an internal enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum Priority: int {
		case Low = 1;
		case High = 2;
	}
}

namespace App {
	use Vendor\Priority;

	#[Priority]
	class Foo {}
}
```

## Why is it reported?

An attribute references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in your attributes creates a fragile dependency on implementation details that can change without notice.

Enums cannot be used as attribute classes, so using an enum in an attribute context is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) attribute class instead:

```diff-php
 namespace App;

-use Vendor\Priority;
+use Vendor\PublicAttribute;

-#[Priority]
+#[PublicAttribute]
 class Foo {}
```

If no public alternative exists, define your own attribute class.
