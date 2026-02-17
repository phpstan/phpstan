---
title: "sealed.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewHandler instead */
class OldHandler
{
}

/**
 * @phpstan-sealed OldHandler
 */
interface HandlerProvider
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@phpstan-sealed` PHPDoc tag references a class that is marked as `@deprecated`. The `@phpstan-sealed` tag restricts which types are allowed to implement or extend the sealed type. Referencing a deprecated class in this tag creates a dependency on a symbol that is scheduled for removal.

## How to fix it

Replace the deprecated class with its recommended replacement in the `@phpstan-sealed` tag:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 /**
- * @phpstan-sealed OldHandler
+ * @phpstan-sealed NewHandler
  */
 interface HandlerProvider
 {
 }
```

If the containing declaration is itself deprecated, the error will not be reported.
