---
title: "class.extendsInternalInterface"
shortDescription: "Class extends an internal interface."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	class MyClass extends \Vendor\InternalInterface {}
}
```

## Why is it reported?

The class extends a type that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions.

A class cannot extend an interface in PHP -- it should use `implements` instead. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Use `implements` instead of `extends`, and use a non-internal interface:

```diff-php
-class MyClass extends \Vendor\InternalInterface {}
+class MyClass implements \Vendor\PublicInterface {}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
