---
title: "requireImplements.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewService instead */
class OldService
{
}

/**
 * @phpstan-require-implements OldService
 */
interface ServiceAware
{
}
```

## Why is it reported?

A `@phpstan-require-implements` PHPDoc tag references a class that has been marked as `@deprecated`. This means the trait or interface is requiring implementing classes to depend on a type that is scheduled for removal. The deprecation notice typically indicates a replacement exists.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated class with its recommended replacement in the `@phpstan-require-implements` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-implements OldService
+ * @phpstan-require-implements NewService
  */
 interface ServiceAware
 {
 }
```

If the deprecated class has no direct replacement, consider restructuring the code to remove the `@phpstan-require-implements` constraint, or update it to reference the new replacement type that serves the same role.
