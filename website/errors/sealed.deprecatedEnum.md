---
title: "sealed.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewStatus instead */
enum OldStatus
{
    case Active;
    case Inactive;
}

/**
 * @phpstan-sealed OldStatus
 */
interface StatusProvider
{
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

The `@phpstan-sealed` PHPDoc tag references an enum that is marked as `@deprecated`. The `@phpstan-sealed` tag restricts which classes or types can be part of a sealed type hierarchy. Referencing a deprecated enum in this tag creates a dependency on a symbol that is scheduled for removal.

## How to fix it

Replace the deprecated enum with its recommended replacement in the `@phpstan-sealed` tag:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 /**
- * @phpstan-sealed OldStatus
+ * @phpstan-sealed NewStatus
  */
 interface StatusProvider
 {
 }
```

If the containing declaration is itself deprecated, the error will not be reported.
