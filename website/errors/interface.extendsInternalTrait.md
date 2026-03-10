---
title: "interface.extendsInternalTrait"
shortDescription: "Interface extends a trait marked as @internal."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait HelperTrait {}

	class Foo {
		use HelperTrait;
	}
}

namespace App {
	interface MyInterface extends \Vendor\HelperTrait {}
}
```

## Why is it reported?

The interface declaration attempts to extend a trait that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice.

Interfaces cannot extend traits in PHP -- interfaces can only extend other interfaces. This code is invalid regardless of the `@internal` annotation.

## How to fix it

Extend a public (non-internal) interface instead:

```diff-php
-interface MyInterface extends \Vendor\HelperTrait {}
+interface MyInterface extends \Vendor\PublicInterface {}
```

If you need the functionality provided by the trait, define the methods directly in the interface and have implementing classes use the trait separately.
