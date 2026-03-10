---
title: "sealed.internalClass"
shortDescription: "Tag @phpstan-sealed references an internal class."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalChild {}
}

namespace App {
	/** @phpstan-sealed \Vendor\InternalChild */
	class MyBase {}
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of their package and may change or be removed at any time. Referencing them in a `@phpstan-sealed` tag creates a dependency on an implementation detail that could break without notice.

## How to fix it

Reference only public (non-internal) classes in `@phpstan-sealed` tags:

```diff-php
-/** @phpstan-sealed \Vendor\InternalChild */
+/** @phpstan-sealed \Vendor\PublicChild */
 class MyBase {}
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
