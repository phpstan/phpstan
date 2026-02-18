---
title: "methodTag.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Assume InternalInterface is marked @internal in another package

/**
 * @method \Vendor\InternalInterface createHandler()
 */
class HandlerFactory
{
	// error: PHPDoc tag @method for createHandler() references
	//        internal interface Vendor\InternalInterface.
}
```

## Why is it reported?

The `@method` PHPDoc tag declares a magic method whose signature references an interface that is marked as `@internal` in another package. Internal types are not part of the public API and may change or be removed without notice. Depending on them from outside the package creates fragile coupling.

## How to fix it

Replace the internal interface reference with a public API type from the package, or define a local interface that mirrors the needed contract.

```diff-php
 /**
- * @method \Vendor\InternalInterface createHandler()
+ * @method \Vendor\HandlerInterface createHandler()
  */
 class HandlerFactory
 {
 }
```
