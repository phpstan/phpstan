---
title: "sealed.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// /** @internal */
// class InternalHandler {}

use ThirdParty\InternalHandler;

/**
 * @phpstan-sealed InternalHandler
 */
interface HandlerProvider
{
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references a class that is marked as `@internal` in another package. Internal classes are not part of the public API of their package and may change or be removed at any time. Referencing them in a `@phpstan-sealed` tag creates a dependency on an implementation detail that could break without notice.

## How to fix it

Reference only public (non-internal) classes in `@phpstan-sealed` tags:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalHandler;
+use ThirdParty\PublicHandler;

 /**
- * @phpstan-sealed InternalHandler
+ * @phpstan-sealed PublicHandler
  */
 interface HandlerProvider
 {
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
