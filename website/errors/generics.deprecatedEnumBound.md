---
title: "generics.deprecatedEnumBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

/**
 * @template T of OldStatus
 */
class StatusHandler
{
}
```

## Why is it reported?

The `@template` tag uses a deprecated enum as its bound constraint (`T of OldStatus`). The enum `OldStatus` is marked as `@deprecated`, meaning it should no longer be used and may be removed in a future version.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## How to fix it

Replace the deprecated enum with its recommended replacement:

```diff-php
 /**
- * @template T of OldStatus
+ * @template T of NewStatus
  */
 class StatusHandler
 {
 }
```
