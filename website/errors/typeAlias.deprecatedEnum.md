---
title: "typeAlias.deprecatedEnum"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use NewStatus instead
 */
enum OldStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

/**
 * @phpstan-type StatusAlias OldStatus
 */
class Config
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` references an enum that is marked as `@deprecated`. Using deprecated symbols in type aliases propagates the dependency on the deprecated code, making future migration harder.

## How to fix it

Update the type alias to reference the non-deprecated replacement.

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type StatusAlias OldStatus
+ * @phpstan-type StatusAlias NewStatus
  */
 class Config
 {
 }
```
