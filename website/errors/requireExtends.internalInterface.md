---
title: "requireExtends.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
interface InternalInterface
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalInterface;

/**
 * @phpstan-require-extends InternalInterface
 */
interface MyInterface
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references an interface that is marked as `@internal`. Internal interfaces are not part of the public API of the package that defines them and may change or be removed without notice. Depending on them in a `@phpstan-require-extends` constraint creates a fragile dependency on implementation details.

## How to fix it

Use a public (non-internal) interface in the `@phpstan-require-extends` tag:

```diff-php
 /**
- * @phpstan-require-extends InternalInterface
+ * @phpstan-require-extends PublicInterface
  */
 interface MyInterface
 {
 }
```

If the interface is internal to your own project, the error is not reported when the usage is within the same root namespace. Reorganize your namespaces so that internal types are only used within their own namespace boundary.
