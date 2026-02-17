---
title: "enum.implementsInternalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Internal\StatusEnum;

enum MyStatus implements StatusEnum
{
	case Active;
	case Inactive;
}
```

## Why is it reported?

The enum implements an internal enum from another namespace or package. Types marked with `@internal` are not meant to be used outside of the package or root namespace where they are defined. Using an internal enum in the `implements` clause creates a dependency on an implementation detail that may change without notice in future versions.

## How to fix it

Use the public (non-internal) interface or contract provided by the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Some\Internal\StatusEnum;
+use Some\PublicStatusInterface;

-enum MyStatus implements StatusEnum
+enum MyStatus implements PublicStatusInterface
 {
 	case Active;
 	case Inactive;
 }
```

If no public alternative exists, contact the package maintainer to request a public API, or define a local interface in the application code.
