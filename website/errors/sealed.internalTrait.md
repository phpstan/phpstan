---
title: "sealed.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// /** @internal */
// trait InternalTrait {}

use ThirdParty\InternalTrait;

/**
 * @phpstan-sealed InternalTrait
 */
interface HandlerProvider
{
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references a trait that is marked as `@internal` in another package. Internal traits are not part of the public API of their package and may change or be removed at any time. Referencing them in a `@phpstan-sealed` tag creates a dependency on an implementation detail that could break without notice.

## How to fix it

Reference only public (non-internal) types in `@phpstan-sealed` tags:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalTrait;
+use ThirdParty\PublicClass;

 /**
- * @phpstan-sealed InternalTrait
+ * @phpstan-sealed PublicClass
  */
 interface HandlerProvider
 {
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
