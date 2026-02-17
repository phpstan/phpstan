---
title: "return.internalTrait"
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

function createObject(): InternalTrait
{
    // ...
}
```

## Why is it reported?

The return type of the function or method references a trait that is marked as `@internal`. Internal symbols are not meant to be used outside the package or namespace that defines them. Referencing an internal trait in a return type exposes an internal implementation detail in a public API, creating a dependency on something that may change without notice.

## How to fix it

Replace the internal trait with a public interface or class in the return type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalTrait;
+use ThirdParty\PublicInterface;

-function createObject(): InternalTrait
+function createObject(): PublicInterface
 {
     // ...
 }
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
