---
title: "typeAlias.internalInterface"
shortDescription: "Type alias references an internal interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	/**
	 * @phpstan-type MyAlias \Vendor\InternalInterface
	 */
	class Config {}
}
```

## Why is it reported?

A PHPStan type alias (`@phpstan-type`) references an interface that is marked as `@internal`. Internal interfaces are not part of a package's public API and may change or be removed without notice. Using them in a type alias creates a hidden dependency on unstable implementation details.

## How to fix it

Replace the internal interface with a public type from the package:

```diff-php
 /**
- * @phpstan-type MyAlias \Vendor\InternalInterface
+ * @phpstan-type MyAlias \Vendor\PublicInterface
  */
 class Config {}
```

If the interface is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
