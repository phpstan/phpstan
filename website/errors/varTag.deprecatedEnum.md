---
title: "varTag.deprecatedEnum"
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
}

/** @var OldStatus $status */
$status = getStatus();
```

## Why is it reported?

The `@var` PHPDoc tag references an enum that is marked as `@deprecated`. Using deprecated symbols in PHPDoc tags maintains a dependency on code that is scheduled for removal and should be migrated to the replacement.

## How to fix it

Update the `@var` tag to reference the non-deprecated replacement enum.

```diff-php
 <?php declare(strict_types = 1);

-/** @var OldStatus $status */
+/** @var NewStatus $status */
 $status = getStatus();
```
