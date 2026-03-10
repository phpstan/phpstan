---
title: "requireImplements.internalEnum"
shortDescription: "Tag @phpstan-require-implements references an internal enum."
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
	 * @phpstan-require-implements \Vendor\InternalEnum
	 */
	trait MyTrait {}
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references an enum that is marked as `@internal`. Internal types are not part of the public API of their package and may change or be removed without notice.

Note: triggering this identifier requires using an enum in `@phpstan-require-implements`, which only accepts interfaces. PHPStan therefore always also reports a `requireImplements.enum` error alongside this one.

## How to fix it

Use a public (non-internal) interface in the `@phpstan-require-implements` tag instead:

```diff-php
 /**
- * @phpstan-require-implements \Vendor\InternalEnum
+ * @phpstan-require-implements \Vendor\PublicInterface
  */
 trait MyTrait {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
