---
title: "new.internalEnum"
shortDescription: "Referencing an internal enum in an instantiation context."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum CacheDriver {
		case Redis;
		case Memcached;
	}
}

namespace App {
	$driver = new \Vendor\CacheDriver(); // error: Instantiation of internal enum Vendor\CacheDriver.
}
```

In practice, this is typically reported as [Cannot instantiate enum](/error-identifiers/new.enum) (`new.enum`) because enums cannot be instantiated at all. The `new.internalEnum` identifier is reported when the internal access violation is the primary concern.

## Why is it reported?

An internal enum is being used in an instantiation context from outside its root namespace. Enums marked as `@internal` are not part of the public API of the package that defines them and are intended to be used only within that package. They may change or be removed without notice in future versions.

## How to fix it

Use the public API provided by the package instead of directly referencing the internal enum:

```diff-php
 namespace App {
-	$driver = new \Vendor\CacheDriver();
+	$driver = \Vendor\CacheFactory::create();
 }
```

If no public alternative exists, contact the package maintainer to request a public API for the functionality needed.
