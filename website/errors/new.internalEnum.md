---
title: "new.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\CacheDriver;

$driver = new CacheDriver(); // ERROR: Instantiation of internal enum Vendor\Internal\CacheDriver.
```

## Why is it reported?

An internal enum (or a class typed as an enum marked with `@internal`) is being instantiated from outside its root namespace. Types marked as `@internal` are not part of the public API of the package that defines them and are intended to be used only within that package. Instantiating internal types creates a dependency on implementation details that may change without notice in future versions.

## How to fix it

Use the public API provided by the package instead of directly instantiating the internal type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\CacheDriver;
+use Vendor\CacheFactory;

-$driver = new CacheDriver();
+$driver = CacheFactory::create();
```

If no public alternative exists, contact the package maintainer to request a public API for the functionality needed.
