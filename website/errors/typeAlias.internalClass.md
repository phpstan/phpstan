---
title: "typeAlias.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// /** @internal */
// class InternalHelper {}

use ThirdParty\InternalHelper;

/**
 * @phpstan-type HelperType InternalHelper
 */
class MyClass
{
}
```

## Why is it reported?

The type alias defined with `@phpstan-type` references a class that is marked as `@internal`. Internal symbols are not meant to be used outside the package or namespace that defines them. Referencing an internal class in a type alias creates a dependency on an implementation detail that may change without notice in future versions of the package.

## How to fix it

Replace the internal class with a public type in the type alias:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalHelper;
+use ThirdParty\PublicHelperInterface;

 /**
- * @phpstan-type HelperType InternalHelper
+ * @phpstan-type HelperType PublicHelperInterface
  */
 class MyClass
 {
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
