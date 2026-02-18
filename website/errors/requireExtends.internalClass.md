---
title: "requireExtends.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\BaseClass;

/**
 * @phpstan-require-extends BaseClass
 */
interface MyInterface
{
}
```

Where `BaseClass` is marked as `@internal` in the `Vendor` package.

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not meant to be used outside the package that defines them. Referencing an internal class in a require-extends constraint creates a dependency on an implementation detail that may change without notice.

## How to fix it

Use a public (non-internal) class from the package instead.

```diff-php
 /**
- * @phpstan-require-extends BaseClass
+ * @phpstan-require-extends PublicBaseClass
  */
 interface MyInterface
 {
 }
```

If no public alternative exists, consider whether the constraint is necessary, or contact the package maintainer to request a public API.
