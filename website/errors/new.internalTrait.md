---
title: "new.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Assume InternalTrait is marked @internal in vendor/some-package

class MyClass
{
	use \Vendor\InternalTrait; // reported via a different identifier
}

/**
 * @method \Vendor\InternalTrait doSomething()
 */
class Factory
{
	// error: PHPDoc tag @method for doSomething() references
	//        internal trait Vendor\InternalTrait.
}
```

## Why is it reported?

The code references a trait that is marked as `@internal` in another package. Internal traits are implementation details of their package and may change or be removed in any release without following semantic versioning. Referencing them from outside the package creates fragile dependencies.

## How to fix it

Avoid referencing internal traits from other packages. Use public API types instead.

```diff-php
 /**
- * @method \Vendor\InternalTrait doSomething()
+ * @method \Vendor\PublicInterface doSomething()
  */
 class Factory
 {
 }
```
