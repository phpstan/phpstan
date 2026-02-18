---
title: "new.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// Assume InternalHelper is marked @internal in vendor/some-package

$helper = new \Vendor\InternalHelper(); // error: Instantiation of internal class Vendor\InternalHelper.
```

## Why is it reported?

The class being instantiated is marked as `@internal`, meaning it is not part of the package's public API. Internal classes may change or be removed in any release without following semantic versioning. Using them from outside the package creates fragile dependencies that can break without warning during updates.

## How to fix it

Use the public API provided by the package instead of instantiating its internal classes directly.

```diff-php
-$helper = new \Vendor\InternalHelper();
+$helper = \Vendor\HelperFactory::create();
```

If no public alternative exists, consider opening a feature request with the package maintainer.
