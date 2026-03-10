---
title: "requireImplements.internalClass"
shortDescription: "Tag @phpstan-require-implements references an internal class."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalHelper {}
}

namespace App {
	/**
	 * @phpstan-require-implements \Vendor\InternalHelper
	 */
	trait HelperAware {}
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not meant to be used outside the package that defines them.

Note: triggering this identifier requires using a class in `@phpstan-require-implements`, which only accepts interfaces. PHPStan therefore always also reports a `requireImplements.class` error alongside this one.

## How to fix it

Use a public (non-internal) interface instead:

```diff-php
 /**
- * @phpstan-require-implements \Vendor\InternalHelper
+ * @phpstan-require-implements \Vendor\PublicHelperInterface
  */
 trait HelperAware {}
```

If the internal class is within the same package and the usage is intentional, the `@internal` tag may need to be reconsidered, or a different design approach should be used.
