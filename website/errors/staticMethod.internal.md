---
title: "staticMethod.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use ThirdParty\Foo;

// ThirdParty\Foo has:
// /** @internal */
// public static function doInternal(): void {}

Foo::doInternal();
```

## Why is it reported?

A static method marked as `@internal` is being called from outside its root namespace. Internal methods are not part of the public API of the package and may change or be removed at any time without following semantic versioning. Calling them from external code creates a fragile dependency.

## How to fix it

Use the public API provided by the package instead:

```diff-php
 namespace App;

 use ThirdParty\Foo;

-Foo::doInternal();
+Foo::doPublicAlternative();
```

If the method is internal to the same package, the error will not be reported. The `@internal` restriction only applies to cross-package usage (calls from outside the root namespace).
