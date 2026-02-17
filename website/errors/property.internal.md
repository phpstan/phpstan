---
title: "property.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

class Service
{
	/** @internal */
	public int $connectionCount = 0;
}
```

```php
<?php declare(strict_types = 1);

namespace External;

use App\Service;

function checkConnections(Service $service): int
{
	return $service->connectionCount; // ERROR: Access to internal property App\Service::$connectionCount from outside its root namespace App.
}
```

## Why is it reported?

The code accesses a property that has been marked as `@internal` in its PHPDoc. Internal properties are implementation details of a package or namespace and are not meant to be used by external code. They may change or be removed without notice in future versions.

PHPStan checks internal access based on the root namespace. Code within the same root namespace is allowed to access internal properties, but code from a different root namespace is not.

## How to fix it

Use the public API of the class instead of accessing internal properties:

```diff-php
 <?php declare(strict_types = 1);

 namespace External;

 use App\Service;

 function checkConnections(Service $service): int
 {
-	return $service->connectionCount;
+	return $service->getConnectionCount();
 }
```

If the class does not provide a public method for the information, consider requesting one from the library maintainer, or use a different approach that does not rely on internal implementation details.
