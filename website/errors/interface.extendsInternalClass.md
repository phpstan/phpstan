---
title: "interface.extendsInternalClass"
shortDescription: "Interface extends an internal class from another package."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalClass {}
}

namespace App {
	interface MyInterface extends \Vendor\InternalClass {}
}
```

## Why is it reported?

An interface extends a type that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice. Depending on internal types in your interface definitions makes your code fragile.

An interface cannot extend a class in PHP -- interfaces can only extend other interfaces. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Extend a public (non-internal) interface instead:

```diff-php
-interface MyInterface extends \Vendor\InternalClass {}
+interface MyInterface extends \Vendor\PublicInterface {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
