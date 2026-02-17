---
title: "generics.deprecatedEnumDefault"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use NewStatus instead.
 */
enum OldStatus
{
    case Active;
}

/**
 * @template T = OldStatus
 */
class Container
{
}
```

## Why is it reported?

A `@template` tag uses a deprecated enum as its default type. Since the enum is deprecated, code relying on this default will break when the enum is removed.

## How to fix it

Replace the deprecated enum with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = OldStatus
+ * @template T = NewStatus
  */
 class Container
 {
 }
```
