---
title: "new.internalInterface"
shortDescription: "Referencing an internal interface in an instantiation context."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface Handler {
		public function handle(): void;
	}
}

namespace App {
	$handler = new \Vendor\Handler(); // error: Instantiation of internal interface Vendor\Handler.
}
```

In practice, this is typically reported as [Cannot instantiate interface](/error-identifiers/new.interface) (`new.interface`) because interfaces cannot be instantiated at all. The `new.internalInterface` identifier is reported when the internal access violation is the primary concern.

## Why is it reported?

An internal interface is being used in an instantiation context from outside its root namespace. Interfaces marked as `@internal` are not meant to be used outside of the package or namespace where they are defined. They may change or be removed without notice in future versions.

## How to fix it

Use a public (non-internal) type and a factory or concrete class from the package's public API instead:

```diff-php
 namespace App {
-	$handler = new \Vendor\Handler();
+	$handler = \Vendor\HandlerFactory::create();
 }
```

If you control the internal interface, consider making it public or providing a public alternative.
