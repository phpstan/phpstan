---
title: "mixin.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\RepositoryInterface;

/**
 * @mixin RepositoryInterface
 */
class Foo // ERROR: PHPDoc tag @mixin references internal interface Vendor\Internal\RepositoryInterface.
{
}
```

## Why is it reported?

The `@mixin` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. Referencing internal types in PHPDoc tags creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public (non-internal) interface or class provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\RepositoryInterface;
+use Vendor\PublicRepositoryInterface;

 /**
- * @mixin RepositoryInterface
+ * @mixin PublicRepositoryInterface
  */
 class Foo
 {
 }
```

If no public alternative exists, remove the `@mixin` tag and implement the needed methods directly in the class.
