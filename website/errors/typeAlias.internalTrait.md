---
title: "typeAlias.internalTrait"
shortDescription: "Type alias references an internal trait."
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
	 * @phpstan-type MyAlias \Vendor\InternalTrait
	 */
	class Config {}
}
```

## Why is it reported?

A PHPStan type alias (`@phpstan-type`) references a trait that is marked as `@internal`. Internal traits are not part of a package's public API and may change or be removed without notice.

Note: triggering this identifier requires using a trait in a type alias, which is not a valid type. PHPStan therefore always also reports a `typeAlias.trait` error alongside this one.

## How to fix it

Replace the internal trait with a public type from the package:

```diff-php
 /**
- * @phpstan-type MyAlias \Vendor\InternalTrait
+ * @phpstan-type MyAlias \Vendor\PublicType
  */
 class Config {}
```

If no public alternative exists, consider whether the type alias is necessary, or contact the package maintainer to request a public API.
