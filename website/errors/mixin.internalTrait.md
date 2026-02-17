---
title: "mixin.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\HelperTrait;

/**
 * @mixin HelperTrait
 */
class Foo // ERROR: PHPDoc tag @mixin references internal trait HelperTrait.
{
}
```

## Why is it reported?

The `@mixin` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. Referencing internal types in PHPDoc tags creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public class or interface provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\HelperTrait;
+use Some\PublicHelper;

 /**
- * @mixin HelperTrait
+ * @mixin PublicHelper
  */
 class Foo
 {
 }
```

If no public alternative exists, remove the `@mixin` tag and implement the needed methods directly in the class.
