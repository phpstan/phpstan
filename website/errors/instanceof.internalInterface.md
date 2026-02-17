---
title: "instanceof.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\InternalInterface;

function checkHandler(object $obj): void
{
	if ($obj instanceof InternalInterface) { // ERROR: Instanceof references internal interface InternalInterface.
		// ...
	}
}
```

## Why is it reported?

The interface used in the `instanceof` expression has been marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. Relying on internal types in `instanceof` checks creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public interface or class provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\InternalInterface;
+use Some\PublicInterface;

 function checkHandler(object $obj): void
 {
-	if ($obj instanceof InternalInterface) {
+	if ($obj instanceof PublicInterface) {
 		// ...
 	}
 }
```

If no public alternative exists, contact the package maintainer to request a public API.
