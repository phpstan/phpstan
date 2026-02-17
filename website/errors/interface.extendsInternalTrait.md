---
title: "interface.extendsInternalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\HelperTrait;

interface MyInterface extends HelperTrait // ERROR: Interface MyInterface extends internal trait HelperTrait.
{
}
```

## Why is it reported?

The interface declaration attempts to extend a trait that is marked as `@internal`. This is problematic for two reasons: first, interfaces cannot extend traits in PHP (this is a language-level error), and second, the trait is internal to its package and not meant to be referenced outside of it. Internal types may change without notice in future versions.

## How to fix it

Interfaces can only extend other interfaces. Replace the trait reference with the correct interface:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\HelperTrait;
+use Some\PublicInterface;

-interface MyInterface extends HelperTrait
+interface MyInterface extends PublicInterface
 {
 }
```

If you need the functionality provided by the trait, define the methods directly in the interface and have implementing classes use the trait separately.
