---
title: "requireExtends.internalInterface"
shortDescription: "@phpstan-require-extends references an internal interface."
ignorable: true
unlikely: true
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
	 * @phpstan-require-extends \Vendor\InternalInterface
	 */
	interface MyInterface {}
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of their package and may change or be removed without notice.

Note: triggering this identifier requires using an interface in `@phpstan-require-extends`, which only accepts classes. PHPStan therefore always also reports a `requireExtends.interface` error alongside this one.

## How to fix it

If the intent is to require implementing an interface, use `@phpstan-require-implements` instead:

```diff-php
 /**
- * @phpstan-require-extends \Vendor\InternalInterface
+ * @phpstan-require-implements \Vendor\PublicInterface
  */
 interface MyInterface {}
```

If a class constraint is needed, use a public (non-internal) class:

```diff-php
 /**
- * @phpstan-require-extends \Vendor\InternalInterface
+ * @phpstan-require-extends \Vendor\PublicBaseClass
  */
 interface MyInterface {}
```
