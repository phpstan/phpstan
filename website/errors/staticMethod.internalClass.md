---
title: "staticMethod.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Internal\CacheDriver;

$data = CacheDriver::getAll();
```

## Why is it reported?

A static method is being called on a class that is marked as `@internal`. Internal classes are not meant to be used outside of their own package, and calling methods on them creates a dependency on an implementation detail that can change at any time without following semantic versioning.

## How to fix it

Use the public API provided by the package instead of accessing internal classes directly.

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Internal\CacheDriver;
+use Public\Cache;

-$data = CacheDriver::getAll();
+$data = Cache::getAll();
```
