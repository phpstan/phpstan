---
title: "typeAlias.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Vendor\Internal\SomeTrait;

/**
 * @phpstan-type MyType SomeTrait
 */
class Foo
{
}
```

Where `SomeTrait` is marked as `@internal` in the `Vendor` package.

## Why is it reported?

A PHPStan type alias (`@phpstan-type`) references a trait that is marked as `@internal`. Internal traits are not part of a package's public API and may change or be removed without notice. Using them in a type alias creates a hidden dependency on unstable implementation details.

## How to fix it

Replace the internal trait with a public type from the package.

```diff-php
 /**
- * @phpstan-type MyType SomeTrait
+ * @phpstan-type MyType PublicType
  */
 class Foo
 {
 }
```

If no public alternative exists, consider whether the type alias is necessary, or contact the package maintainer to request a public API.
