---
title: "requireImplements.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-library/src/InternalHelper.php:
namespace SomeLibrary;

/** @internal */
trait InternalHelper
{
	public function help(): void {}
}

// In src/MyTrait.php:
namespace App;

/**
 * @phpstan-require-implements \SomeLibrary\InternalHelper
 */
trait MyTrait // ERROR: PHPDoc tag @phpstan-require-implements references internal trait SomeLibrary\InternalHelper.
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references a trait that is marked as `@internal`. Internal symbols are not meant to be used outside the package that defines them, as they can change or be removed without notice. Relying on internal types in requirement tags creates a fragile dependency.

## How to fix it

Replace the internal trait reference with a public API type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements \SomeLibrary\InternalHelper
+ * @phpstan-require-implements \SomeLibrary\HelperInterface
  */
 trait MyTrait
 {
 }
```

If there is no public replacement available, consider whether the requirement is truly needed or contact the library maintainers to request a public API for this use case.
