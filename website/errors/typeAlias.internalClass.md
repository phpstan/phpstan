---
title: "typeAlias.internalClass"
shortDescription: "Type alias references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalType {}
}

namespace App {
	/**
	 * @phpstan-type MyAlias \Vendor\InternalType
	 */
	class Config {}
}
```

## Why is it reported?

The type alias defined with `@phpstan-type` references a class that is marked as `@internal`. Internal classes are not meant to be used outside the package that defines them. Referencing an internal class in a type alias creates a dependency on an implementation detail that may change without notice in future versions of the package.

## How to fix it

Replace the internal class with a public type in the type alias:

```diff-php
 /**
- * @phpstan-type MyAlias \Vendor\InternalType
+ * @phpstan-type MyAlias \Vendor\PublicType
  */
 class Config {}
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
