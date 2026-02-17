---
title: "varTag.deprecatedClass"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @deprecated Use NewHelper instead
 */
class OldHelper
{
	public function help(): void
	{
	}
}

/** @var OldHelper $helper */
$helper = getHelper();
```

## Why is it reported?

The `@var` PHPDoc tag references a class that is marked as `@deprecated`. Using deprecated classes in PHPDoc tags maintains a dependency on code that is scheduled for removal and should be migrated to the replacement.

## How to fix it

Update the `@var` tag to reference the non-deprecated replacement class:

```diff-php
 <?php declare(strict_types = 1);

-/** @var OldHelper $helper */
+/** @var NewHelper $helper */
 $helper = getHelper();
```
