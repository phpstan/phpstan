---
title: "requireImplements.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-lib:

/** @internal */
class InternalHelper
{
}

// In your code:

/**
 * @phpstan-require-implements InternalHelper
 */
interface HelperAware
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag references a class that is marked as `@internal`. Internal classes are not meant to be used outside the package or namespace they belong to. Referencing an internal class in a `@phpstan-require-implements` tag creates a dependency on an implementation detail that may change without notice.

## How to fix it

Replace the internal class with a public API type that serves the same purpose:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements InternalHelper
+ * @phpstan-require-implements PublicHelperInterface
  */
 interface HelperAware
 {
 }
```

If the internal class is within the same package and the usage is intentional, the `@internal` tag may need to be reconsidered, or the `@phpstan-require-implements` tag should be removed in favor of a different design approach.
