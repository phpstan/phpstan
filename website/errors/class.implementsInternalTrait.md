---
title: "class.implementsInternalTrait"
shortDescription: "Class implements a trait marked as @internal."
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
	class MyClass implements \Vendor\InternalTrait {}
}
```

## Why is it reported?

The class uses a trait in its `implements` clause that is marked as `@internal`. Internal types are not part of the library's public API and may change or be removed without notice in future versions.

A class cannot implement a trait in PHP -- traits should be used with the `use` keyword inside a class body instead. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Use a public (non-internal) interface instead, or use the `use` keyword for traits:

```diff-php
-class MyClass implements \Vendor\InternalTrait {}
+class MyClass implements \Vendor\PublicInterface {}
```
