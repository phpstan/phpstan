---
title: "return.internalEnum"
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

function getStatus(): InternalStatus
{
    // ...
}
```

## Why is it reported?

The native return type declaration of a function or method references an enum that is marked as `@internal`. Internal enums are not part of the public API of the package that defines them. Exposing an internal enum in a return type creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal enum with a public enum or interface from the package in the return type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalStatus;
+use ThirdParty\Status;

-function getStatus(): InternalStatus
+function getStatus(): Status
 {
     // ...
 }
```

If the enum is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
