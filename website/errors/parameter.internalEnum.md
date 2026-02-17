---
title: "parameter.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\Status;

function doFoo(Status $status): void // ERROR: Parameter $status has typehint with internal enum Vendor\Internal\Status.
{
}
```

## Why is it reported?

The parameter type hint references an enum that is marked as `@internal`. Internal enums are not part of the public API and are intended for use only within the package or namespace where they are defined. Using an internal enum as a parameter type outside its root namespace means the code depends on an implementation detail that may change or be removed without notice.

## How to fix it

Use the public API provided by the package instead of referencing internal enums directly:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\Status;
+use Vendor\PublicStatus;

-function doFoo(Status $status): void
+function doFoo(PublicStatus $status): void
 {
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
