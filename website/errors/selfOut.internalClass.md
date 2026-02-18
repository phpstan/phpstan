---
title: "selfOut.internalClass"
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

class MyClass
{
    /**
     * @phpstan-self-out self&InternalHelper
     */
    public function apply(): void
    {
        // ...
    }
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not part of the public API of their package and may change or be removed at any time. Referencing them in a `@phpstan-self-out` tag creates a dependency on an internal implementation detail that may break without notice.

## How to fix it

Replace the internal class with a public type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalHelper;
+use ThirdParty\PublicInterface;

 class MyClass
 {
     /**
-     * @phpstan-self-out self&InternalHelper
+     * @phpstan-self-out self&PublicInterface
      */
     public function apply(): void
     {
         // ...
     }
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
