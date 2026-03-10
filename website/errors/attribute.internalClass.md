---
title: "attribute.internalClass"
shortDescription: "Attribute references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	#[\Attribute]
	class InternalAttribute {}
}

namespace App {
	use Vendor\InternalAttribute;

	#[InternalAttribute]
	class Foo {}
}
```

## Why is it reported?

An attribute references a class that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal classes in your attributes creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) attribute class instead:

```diff-php
 namespace App;

-use Vendor\InternalAttribute;
+use Vendor\PublicAttribute;

-#[InternalAttribute]
+#[PublicAttribute]
 class Foo {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
