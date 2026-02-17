---
title: "method.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\Status;

function doFoo(Status $status): void
{
	$status->label(); // ERROR: Call to method label() of internal enum Vendor\Internal\Status from outside its root namespace Vendor.
}
```

## Why is it reported?

A method is being called on an object whose type is an enum marked as `@internal`. Internal enums are not part of the public API and are intended to be used only within the package or namespace where they are defined. Calling methods on an internal enum from outside its root namespace violates this contract. The enum may change or be removed without notice in future versions of the package.

## How to fix it

Use the public API provided by the package instead of accessing internal enums directly:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\Status;
+use Vendor\PublicStatus;

-function doFoo(Status $status): void
+function doFoo(PublicStatus $status): void
 {
 	$status->label();
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
