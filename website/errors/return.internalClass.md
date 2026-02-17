---
title: "return.internalClass"
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

function createHelper(): InternalHelper
{
    // ...
}
```

## Why is it reported?

The native return type declaration of a function or method references a class that is marked as `@internal`. Internal classes are not part of the public API of the package that defines them. Using an internal class in a return type exposes an implementation detail to callers, creating a dependency on something that may change or be removed without notice.

## How to fix it

Replace the internal class with a public class or interface from the package in the return type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalHelper;
+use ThirdParty\HelperInterface;

-function createHelper(): InternalHelper
+function createHelper(): HelperInterface
 {
     // ...
 }
```

If the class is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
