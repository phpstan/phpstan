---
title: "requireExtends.internalEnum"
shortDescription: "@phpstan-require-extends references an internal enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

namespace Vendor {
	/** @internal */
	enum InternalEnum {
		case A;
	}
}

namespace App {
	/**
	 * @phpstan-require-extends \Vendor\InternalEnum
	 */
	interface MyInterface {}
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not part of the public API of their package and may change or be removed without notice.

Note: triggering this identifier requires using an enum in `@phpstan-require-extends`, which only accepts classes. PHPStan therefore always also reports a `requireExtends.enum` error alongside this one.

## How to fix it

Use a public (non-internal) class in the `@phpstan-require-extends` tag instead:

```diff-php
 /**
- * @phpstan-require-extends \Vendor\InternalEnum
+ * @phpstan-require-extends \Vendor\PublicBaseClass
  */
 interface MyInterface {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
