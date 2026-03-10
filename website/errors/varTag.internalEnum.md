---
title: "varTag.internalEnum"
shortDescription: "@var PHPDoc tag references an internal enum from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum InternalEnum {
		case A;
	}
}

namespace App {
	function getStatus(): object {
		return new \stdClass();
	}

	/** @var \Vendor\InternalEnum $status */
	$status = getStatus();
}
```

## Why is it reported?

The `@var` PHPDoc tag references an enum that is marked as `@internal` in another package. Internal symbols are not meant to be used outside of their own package. Referencing them in `@var` tags creates a dependency on implementation details that can change without notice.

## How to fix it

Use the public API type instead of the internal enum in the `@var` tag:

```diff-php
 namespace App {
-	/** @var \Vendor\InternalEnum $status */
+	/** @var \Vendor\PublicEnum $status */
 	$status = getStatus();
 }
```
