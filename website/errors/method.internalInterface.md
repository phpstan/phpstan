---
title: "method.internalInterface"
shortDescription: "Called method belongs to an interface marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface Logger {
		public function log(string $message): void;
	}
}

namespace App {
	function test(\Vendor\Logger $logger): void {
		$logger->log('hello'); // error: Call to method log() of internal interface Vendor\Logger from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A method is being called on an object whose type is an interface marked with the `@internal` PHPDoc tag. Internal interfaces are not part of the public API and are intended to be used only within the package or namespace where they are defined. Calling methods on an internal interface from outside its root namespace violates this contract. The interface may change or be removed without notice in future versions of the package.

## How to fix it

Use the public API provided by the package instead of accessing internal interfaces directly:

```diff-php
 namespace App {
-	function test(\Vendor\Logger $logger): void {
+	function test(\Vendor\PublicLogger $logger): void {
 		$logger->log('hello');
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
