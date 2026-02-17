---
title: "staticMethod.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// /** @internal */
// trait InternalTrait {
//     public static function helperMethod(): string { return 'result'; }
// }

use ThirdParty\InternalTrait;

InternalTrait::helperMethod();
```

## Why is it reported?

A static method is being called on a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without prior notice. Calling static methods on internal traits creates a dependency on implementation details that should not be relied upon.

## How to fix it

Use the public API of the package instead of calling static methods on internal traits:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalTrait;
+use ThirdParty\PublicHelper;

-InternalTrait::helperMethod();
+PublicHelper::helperMethod();
```

If the trait is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage.
