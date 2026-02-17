---
title: "staticMethod.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Internal\SomeInterface;

// In another package's code:
// namespace Internal;
// /** @internal */
// interface SomeInterface {
//     public static function create(): static;
// }

SomeInterface::create();
```

## Why is it reported?

A static method is being called on an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them. They may change or be removed in any version without prior notice.

This error can be triggered either because the interface itself is internal, or because the static method is called on a class whose declaring class is an internal interface.

## How to fix it

Use the public API of the package instead of calling static methods on internal interfaces:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Internal\SomeInterface;
+use Public\SomeFactory;

-SomeInterface::create();
+SomeFactory::create();
```

If the interface is internal to your own project and you are calling it from the same root namespace, the error will not be reported. Reorganize your code so that internal interfaces are only used within their own namespace.
