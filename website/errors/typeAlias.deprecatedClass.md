---
title: "typeAlias.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

/** @deprecated Use NewService instead */
class OldService
{
}

/**
 * @phpstan-type ServiceType OldService
 */
class ServiceFactory
{
}
```

## Why is it reported?

This error is reported by the `phpstan/phpstan-deprecation-rules` package.

A PHPStan type alias (defined with `@phpstan-type` or referenced via `@phpstan-import-type`) references a class that is marked as `@deprecated`. Using a deprecated class in a type alias creates a dependency on a symbol that is scheduled for removal.

## How to fix it

Replace the deprecated class in the type alias with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 /**
- * @phpstan-type ServiceType OldService
+ * @phpstan-type ServiceType NewService
  */
 class ServiceFactory
 {
 }
```

If the class containing the type alias is itself deprecated, the error will not be reported.
