---
title: "instanceof.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\StatusEnum;

function checkStatus(object $obj): void
{
	if ($obj instanceof StatusEnum) { // ERROR: Instanceof references internal enum StatusEnum.
		// ...
	}
}
```

## Why is it reported?

The class used in the `instanceof` expression is an enum that has been marked as `@internal`. Internal enums are not part of the public API of the package that defines them. Relying on internal types in `instanceof` checks creates a dependency on implementation details that may change without notice in future versions of the package.

## How to fix it

Use a public interface or class provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\StatusEnum;
+use Some\PublicStatusInterface;

 function checkStatus(object $obj): void
 {
-	if ($obj instanceof StatusEnum) {
+	if ($obj instanceof PublicStatusInterface) {
 		// ...
 	}
 }
```

If no public alternative exists, contact the package maintainer to request a public API.
