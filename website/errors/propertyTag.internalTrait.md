---
title: "propertyTag.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor\Internal;

/** @internal */
trait HelperTrait
{
}
```

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\HelperTrait;

/**
 * @property HelperTrait $helper // ERROR: PHPDoc tag @property references internal trait HelperTrait.
 */
class Foo
{
}
```

## Why is it reported?

The `@property` PHPDoc tag references a trait that has been marked as `@internal`. Internal traits are implementation details of a package or namespace and are not meant to be used by external code. They may change or be removed without notice in future versions.

Additionally, traits are not valid types in PHP, so using one as a type in a `@property` tag is problematic regardless of the internal status.

## How to fix it

Replace the internal trait reference in the `@property` tag with a valid, public type such as an interface or class:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\HelperTrait;
+use Vendor\HelperInterface;

 /**
- * @property HelperTrait $helper
+ * @property HelperInterface $helper
  */
 class Foo
 {
 }
```
