---
title: "sealed.internalInterface"
shortDescription: "Tag @phpstan-sealed references an internal interface."
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
	/** @phpstan-sealed \Vendor\InternalInterface */
	class MyBase {}
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of their package and may change or be removed without notice. Referencing an internal interface in a `@phpstan-sealed` tag creates a dependency on an implementation detail that could break at any time.

## How to fix it

Use a public (non-internal) interface in the `@phpstan-sealed` tag:

```diff-php
-/** @phpstan-sealed \Vendor\InternalInterface */
+/** @phpstan-sealed \Vendor\PublicInterface */
 class MyBase {}
```

If the interface is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
