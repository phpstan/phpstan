---
title: "class.extendsInternalTrait"
shortDescription: "Class extends an internal trait."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {}

	class Foo {
		use InternalTrait;
	}
}

namespace App {
	class MyClass extends \Vendor\InternalTrait {}
}
```

## Why is it reported?

The class extends a type that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions.

Traits cannot be extended in PHP -- they should be used with the `use` keyword inside a class body instead. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Do not extend traits. If the functionality is needed, use the `use` keyword with a non-internal trait:

```diff-php
-class MyClass extends \Vendor\InternalTrait {}
+class MyClass
+{
+	// Use the package's public API instead
+}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
