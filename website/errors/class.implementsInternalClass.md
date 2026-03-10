---
title: "class.implementsInternalClass"
shortDescription: "Class implements an internal class."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class InternalClass {}
}

namespace App {
	class MyClass implements \Vendor\InternalClass {}
}
```

## Why is it reported?

The class uses a class in its `implements` clause that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions.

A class cannot implement another class in PHP -- only interfaces can be implemented. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) interface instead:

```diff-php
-class MyClass implements \Vendor\InternalClass {}
+class MyClass implements \Vendor\PublicInterface {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
