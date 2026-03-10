---
title: "method.internalEnum"
shortDescription: "Called method belongs to an enum marked as @internal."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum Status: string {
		case Active = 'active';
		case Inactive = 'inactive';

		public function label(): string {
			return $this->value;
		}
	}
}

namespace App {
	function test(\Vendor\Status $status): string {
		return $status->label(); // error: Call to method label() of internal enum Vendor\Status from outside its root namespace Vendor.
	}
}
```

## Why is it reported?

A method is being called on an object whose type is an enum marked as `@internal`. Internal enums are not part of the public API and are intended to be used only within the package or namespace where they are defined. Calling methods on an internal enum from outside its root namespace violates this contract. The enum may change or be removed without notice in future versions of the package.

## How to fix it

Use the public API provided by the package instead of accessing internal enums directly:

```diff-php
 namespace App {
-	function test(\Vendor\Status $status): string {
-		return $status->label();
+	function test(\Vendor\PublicStatus $status): string {
+		return $status->label();
 	}
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for the functionality needed.
