---
title: "requireExtends.internalClass"
shortDescription: "@phpstan-require-extends references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalBase {}
}

namespace App {
	/**
	 * @phpstan-require-extends \Vendor\InternalBase
	 */
	interface MyInterface {}
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of their package and may change or be removed without notice. Referencing an internal class in a require-extends constraint creates a dependency on an implementation detail that may break at any time.

## How to fix it

Use a public (non-internal) class from the package instead:

```diff-php
 /**
- * @phpstan-require-extends \Vendor\InternalBase
+ * @phpstan-require-extends \Vendor\PublicBase
  */
 interface MyInterface {}
```

If no public alternative exists, consider whether the constraint is necessary, or contact the package maintainer to request a public API.
