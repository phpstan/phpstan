---
title: "new.internalTrait"
shortDescription: "Instantiating an internal trait from outside its namespace."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {
		public function doSomething(): void {}
	}
}

namespace App {
	$obj = new \Vendor\InternalTrait(); // error: Instantiation of internal trait Vendor\InternalTrait.
}
```

## Why is it reported?

The code attempts to instantiate a trait marked as `@internal`. Traits cannot be instantiated directly, and on top of that, this trait is an internal implementation detail of its package. Internal traits may change or be removed in any release without following semantic versioning.

## How to fix it

Traits are not meant to be instantiated. Use a class from the package's public API instead:

```diff-php
 namespace App {
-	$obj = new \Vendor\InternalTrait();
+	$obj = new \Vendor\PublicService();
 }
```
