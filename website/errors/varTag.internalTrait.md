---
title: "varTag.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Defined in a third-party package:
// namespace ThirdParty;
// /** @internal */
// trait InternalTrait {}

use ThirdParty\InternalTrait;

/** @var InternalTrait $obj */
$obj = createObject();
```

## Why is it reported?

The `@var` PHPDoc tag references a trait that is marked as `@internal`. Internal traits are not part of the public API of the package that defines them. They may change or be removed in any version without notice.

Using an internal trait from another package in a `@var` tag creates a dependency on an implementation detail that is not guaranteed to be stable.

## How to fix it

Use a public interface or class from the package instead of the internal trait:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use ThirdParty\InternalTrait;
+use ThirdParty\PublicInterface;

-/** @var InternalTrait $obj */
+/** @var PublicInterface $obj */
 $obj = createObject();
```

If the trait is internal to your own project, the error will not be reported when referencing it from within the same root namespace.
