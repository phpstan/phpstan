---
title: "method.internalTrait"
shortDescription: "Called method belongs to a trait marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	trait InternalTrait {
		public function traitMethod(): void {}
	}
}

namespace App {
	/**
	 * @param \Vendor\InternalTrait $obj
	 */
	function test(object $obj): void {
		$obj->traitMethod(); // error: Call to method traitMethod() of internal trait Vendor\InternalTrait from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A method is being called on an object whose declaring type is a trait marked with the `@internal` tag. The trait is an implementation detail of the library and is not part of its public API. Methods originating from internal traits should not be called from outside the library because the trait may change or be removed in future versions.

## How to fix it

Avoid calling methods that come from internal traits. Look for alternative public API methods that provide the same functionality:

```diff-php
 namespace App {
-	/**
-	 * @param \Vendor\InternalTrait $obj
-	 */
-	function test(object $obj): void {
-		$obj->traitMethod();
+	function test(\Vendor\PublicService $obj): void {
+		$obj->publicMethod();
 	}
 }
```
