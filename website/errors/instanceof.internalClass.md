---
title: "instanceof.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\InternalService;

function checkService(object $obj): void
{
	if ($obj instanceof InternalService) { // ERROR: Instanceof references internal class InternalService.
		// ...
	}
}
```

## Why is it reported?

The class used in the `instanceof` expression has been marked as `@internal`. Internal classes are not part of the public API of the package that defines them. Relying on internal types in `instanceof` checks creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public interface or class provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\InternalService;
+use Some\PublicServiceInterface;

 function checkService(object $obj): void
 {
-	if ($obj instanceof InternalService) {
+	if ($obj instanceof PublicServiceInterface) {
 		// ...
 	}
 }
```

If no public alternative exists, contact the package maintainer to request a public API.
