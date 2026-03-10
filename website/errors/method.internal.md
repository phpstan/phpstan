---
title: "method.internal"
shortDescription: "Called method is marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	class Service {
		/** @internal */
		public function doInternal(): void {}

		public function doPublic(): void {}
	}
}

namespace App {
	function test(\Vendor\Service $s): void {
		$s->doInternal(); // error: Call to internal method Vendor\Service::doInternal() from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A method marked with the `@internal` PHPDoc tag is being called from outside its root namespace. Internal methods are implementation details that are not part of the public API. They may change or be removed without notice in future versions, and calling them from external code creates fragile dependencies.

## How to fix it

Use the public API of the class instead of calling its internal methods:

```diff-php
 namespace App {
 	function test(\Vendor\Service $s): void {
-		$s->doInternal();
+		$s->doPublic();
 	}
 }
```

If the class does not provide a public method for the functionality, consider requesting one from the library maintainer.
