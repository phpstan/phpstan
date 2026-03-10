---
title: "class.implementsInternalEnum"
shortDescription: "Class implements an internal enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum Status {
		case Active;
		case Inactive;
	}
}

namespace App {
	class Foo implements \Vendor\Status {}
}
```

## Why is it reported?

The class uses an enum in its `implements` clause that is marked as `@internal`. Internal types are not meant to be used outside the package or namespace where they are defined.

Enums cannot be implemented by classes in PHP. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) interface instead:

```diff-php
-class Foo implements \Vendor\Status {}
+class Foo implements \Vendor\StatusInterface {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
