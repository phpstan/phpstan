---
title: "classConstant.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

$value = OldStatus::Active;
```

## Why is it reported?

The code accesses a constant (or enum case) on an enum that has been marked as `@deprecated`. Deprecated enums are scheduled for removal in a future version and should no longer be used.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## How to fix it

Use the replacement suggested in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

-$value = OldStatus::Active;
+$value = NewStatus::Active;
```
