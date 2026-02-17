---
title: "selfOut.internalTrait"
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

class MyClass
{
    /**
     * @phpstan-self-out self&InternalTrait
     */
    public function applyTrait(): void
    {
        // ...
    }
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a trait that is marked as `@internal`. Internal symbols are not meant to be used outside the package or namespace that defines them. Referencing an internal trait in a `@phpstan-self-out` tag creates a dependency on an internal implementation detail that may change without notice.

## How to fix it

Replace the internal trait with a public interface or class:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalTrait;
+use ThirdParty\PublicInterface;

 class MyClass
 {
     /**
-     * @phpstan-self-out self&InternalTrait
+     * @phpstan-self-out self&PublicInterface
      */
-    public function applyTrait(): void
+    public function applyInterface(): void
     {
         // ...
     }
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
