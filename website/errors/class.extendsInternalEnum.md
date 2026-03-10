---
title: "class.extendsInternalEnum"
shortDescription: "Class extends an internal enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	enum Status {
		case Active;
	}
}

namespace App {
	class MyClass extends \Vendor\Status {}
}
```

## Why is it reported?

The class extends a type that is marked as `@internal`. Internal types are not part of the package's public API and may change or be removed without notice in future versions.

Enums cannot be extended in PHP, so this code is invalid regardless of the `@internal` annotation.

## How to fix it

Do not extend enums. Use the public API of the package instead, or use composition:

```diff-php
-class MyClass extends \Vendor\Status {}
+class MyClass
+{
+	// Use the package's public API instead
+}
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
