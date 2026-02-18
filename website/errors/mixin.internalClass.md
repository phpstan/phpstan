---
title: "mixin.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class QueryHelper
{
	public function execute(): void {}
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\QueryHelper;

/**
 * @mixin QueryHelper
 */
class Repository
{
}
```

## Why is it reported?

The `@mixin` PHPDoc tag references a class that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Depending on internal classes in your `@mixin` declarations creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) class in the `@mixin` tag instead:

```diff-php
 /**
- * @mixin QueryHelper
+ * @mixin PublicQueryInterface
  */
 class Repository
 {
 }
```

If you need the functionality provided by the internal class, check whether the library provides a public API, or implement the methods directly in your class.
