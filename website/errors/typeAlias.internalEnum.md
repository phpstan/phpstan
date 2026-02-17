---
title: "typeAlias.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// /** @internal */
// enum InternalStatus { case Active; case Inactive; }

use ThirdParty\InternalStatus;

/**
 * @phpstan-type StatusList list<InternalStatus>
 */
class MyClass
{
}
```

## Why is it reported?

The type alias defined with `@phpstan-type` references an enum that is marked as `@internal`. Internal symbols are not meant to be used outside the package or namespace that defines them. Referencing an internal enum in a type alias creates a dependency on an implementation detail that may change without notice in future versions of the package.

## How to fix it

Replace the internal enum with a public type in the type alias:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalStatus;
+use ThirdParty\PublicStatusInterface;

 /**
- * @phpstan-type StatusList list<InternalStatus>
+ * @phpstan-type StatusList list<PublicStatusInterface>
  */
 class MyClass
 {
 }
```

If the enum is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
