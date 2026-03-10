---
title: "mixin.internalEnum"
shortDescription: "PHPDoc @mixin tag references an internal enum."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalEnum {
		case A;
	}
}

namespace App {
	/** @mixin \Vendor\InternalEnum */
	class MyClass {}
}
```

## Why is it reported?

A `@mixin` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal types in `@mixin` tags creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type in the `@mixin` tag instead:

```diff-php
 namespace App {
-	/** @mixin \Vendor\InternalEnum */
+	/** @mixin \Vendor\PublicClass */
 	class MyClass {}
 }
```

If the library provides a public alternative, use that. Otherwise, define your own type or remove the `@mixin` tag and implement the needed methods directly.
