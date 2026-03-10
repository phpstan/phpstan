---
title: "requireExtends.internalTrait"
shortDescription: "@phpstan-require-extends references an internal trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {}
}

namespace App {
	/**
	 * @phpstan-require-extends \Vendor\InternalTrait
	 */
	interface MyInterface {}
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of a package's public API and may be changed or removed at any time.

Note: triggering this identifier requires using a trait in `@phpstan-require-extends`, which only accepts classes. PHPStan therefore always also reports a `requireExtends.trait` error alongside this one.

## How to fix it

Use a public (non-internal) class in the `@phpstan-require-extends` tag instead:

```diff-php
 /**
- * @phpstan-require-extends \Vendor\InternalTrait
+ * @phpstan-require-extends \Vendor\PublicBaseClass
  */
 interface MyInterface {}
```

If no public alternative exists, consider whether the constraint is necessary, or contact the package maintainer to request a public API.
