---
title: "traitUse.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// /** @internal */
// interface InternalInterface {}

use ThirdParty\InternalInterface;

class Foo
{
    use InternalInterface;
}
```

## Why is it reported?

The `use` statement inside a class body references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

While using an interface in a `use` statement is already incorrect (only traits can be used this way), the internal access error is also reported because the referenced symbol is internal to another package.

## How to fix it

Only traits can be used in a class body `use` statement. If you need functionality from the library, look for a public trait that provides it:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalInterface;
+use ThirdParty\PublicTrait;

 class Foo
 {
-    use InternalInterface;
+    use PublicTrait;
 }
```

If the interface should be implemented rather than used as a trait, use `implements` instead:

```diff-php
-class Foo
+class Foo implements SomePublicInterface
 {
-    use InternalInterface;
 }
```

If the interface is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
