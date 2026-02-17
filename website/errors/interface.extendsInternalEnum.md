---
title: "interface.extendsInternalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// In package vendor/some-package:

/** @internal */
enum Status
{
	case Active;
	case Inactive;
}

// In your code:

interface StatusInterface extends \Vendor\Status
{

}
```

## Why is it reported?

The interface extends an enum that is marked as `@internal`. Internal enums are not meant to be used outside the package that defines them. Extending an internal enum couples your code to implementation details that may change without notice in future versions of the package.

## How to fix it

Stop extending the internal enum and use a different approach:

```diff-php
-interface StatusInterface extends \Vendor\Status
+interface StatusInterface
 {
+	// Define your own contract instead of relying on internal types
 }
```

If you need to work with values from the internal enum, consider using a public API provided by the package instead.
