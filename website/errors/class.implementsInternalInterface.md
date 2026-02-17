---
title: "class.implementsInternalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-library/src/InternalInterface.php:
// namespace SomeLibrary;
// /** @internal */
// interface InternalInterface {}

// In your code:
namespace App;

use SomeLibrary\InternalInterface;

class Foo implements InternalInterface // reported
{

}
```

## Why is it reported?

The interface in the `implements` clause is marked as `@internal` by its defining library. Internal symbols are implementation details that can change or be removed without notice in any release. Implementing an internal interface from a third-party package creates a fragile dependency that may break unexpectedly.

## How to fix it

Use a public (non-internal) interface provided by the library instead.

```diff-php
-class Foo implements InternalInterface
+class Foo implements PublicInterface
 {

 }
```

If no public alternative exists, consider whether the library provides a different extension mechanism, such as extending a base class or using composition.
