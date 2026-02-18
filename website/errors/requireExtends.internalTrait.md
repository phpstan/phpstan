---
title: "requireExtends.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\BaseTrait;

/**
 * @phpstan-require-extends BaseTrait
 */
interface MyInterface
{
}
```

Where `BaseTrait` is marked as `@internal` in the `Vendor` package.

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of a package's public API and may be changed or removed at any time. Depending on them in a require-extends constraint ties code to unstable implementation details.

## How to fix it

Replace the internal trait reference with a public type from the package.

```diff-php
 /**
- * @phpstan-require-extends BaseTrait
+ * @phpstan-require-extends PublicBaseClass
  */
 interface MyInterface
 {
 }
```

If no public alternative exists, consider whether the constraint is necessary, or contact the package maintainer to request a public API.
