---
title: "varTag.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// /** @internal */
// interface InternalInterface {}

use ThirdParty\InternalInterface;

/** @var InternalInterface $service */
$service = getService();
```

## Why is it reported?

The `@var` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal interface from another package in a `@var` tag creates a dependency on an implementation detail that is not guaranteed to be stable.

## How to fix it

Use a public (non-internal) interface or class from the package instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalInterface;
+use ThirdParty\PublicInterface;

-/** @var InternalInterface $service */
+/** @var PublicInterface $service */
 $service = getService();
```

If the interface is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
