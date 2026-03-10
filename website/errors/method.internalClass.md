---
title: "method.internalClass"
shortDescription: "Called method belongs to a class marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	class Service {
		public function process(): void {}
	}
}

namespace App {
	function doFoo(\Vendor\Service $service): void
	{
		$service->process();
	}
}
```

## Why is it reported?

The method is being called on an object whose class is marked as `@internal`. Internal classes are not part of the public API and are intended to be used only within the package or namespace where they are defined. Calling methods on an internal class from outside its root namespace violates this contract. The class may change or be removed without notice in future versions of the package.

## How to fix it

Use the public API provided by the package instead of accessing internal classes directly:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Vendor\Internal\Service;
+use Vendor\PublicService;

-function doFoo(Service $service): void
+function doFoo(PublicService $service): void
 {
 	$service->process();
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
