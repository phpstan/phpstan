---
title: "catch.internalInterface"
shortDescription: "Catch block references an internal interface from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalExceptionInterface {}
}

namespace App {
	function doFoo(): void
	{
		try {
			throw new \Exception();
		} catch (\Vendor\InternalExceptionInterface $e) {
		}
	}
}
```

## Why is it reported?

A `catch` block references an interface that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Catching internal interfaces creates a dependency on implementation details that may change without notice in future versions.

## How to fix it

Catch a public (non-internal) exception type instead:

```diff-php
 try {
 	throw new \Exception();
-} catch (\Vendor\InternalExceptionInterface $e) {
+} catch (\RuntimeException $e) {
 }
```

If the library provides a public exception class or interface for this purpose, catch that instead.
