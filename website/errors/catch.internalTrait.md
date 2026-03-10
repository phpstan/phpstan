---
title: "catch.internalTrait"
shortDescription: "Catch block references an internal trait from another package."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait SomeInternalTrait {}

	class Foo {
		use SomeInternalTrait;
	}
}

namespace App {
	function doFoo(): void
	{
		try {
			throw new \Exception();
		} catch (\Vendor\SomeInternalTrait $e) {
		}
	}
}
```

## Why is it reported?

A `catch` block references a trait that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Catching internal types creates a dependency on implementation details that may change without notice in future versions.

Traits are not throwable, so catching a trait in a `catch` block is invalid regardless of the `@internal` annotation.

## How to fix it

Catch a public (non-internal) exception class instead:

```diff-php
 try {
 	throw new \Exception();
-} catch (\Vendor\SomeInternalTrait $e) {
+} catch (\RuntimeException $e) {
 }
```
