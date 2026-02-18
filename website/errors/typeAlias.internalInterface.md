---
title: "typeAlias.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\SomeInterface;

/**
 * @phpstan-type MyType SomeInterface
 */
class Foo
{
}
```

Where `SomeInterface` is marked as `@internal` in the `Vendor` package.

## Why is it reported?

A PHPStan type alias (`@phpstan-type`) references an interface that is marked as `@internal`. Internal interfaces are not part of a package's public API and may change or be removed without notice. Using them in a type alias creates a hidden dependency on unstable implementation details.

## How to fix it

Replace the internal interface with a public type from the package.

```diff-php
 /**
- * @phpstan-type MyType SomeInterface
+ * @phpstan-type MyType PublicInterface
  */
 class Foo
 {
 }
```

If no public alternative exists, consider whether the type alias is necessary, or contact the package maintainer to request a public API.
