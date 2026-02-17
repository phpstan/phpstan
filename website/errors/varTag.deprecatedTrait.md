---
title: "varTag.deprecatedTrait"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void
	{
	}
}

/** @var OldHelper $helper */
$helper = getHelper();
```

## Why is it reported?

The `@var` PHPDoc tag references a trait that is marked as `@deprecated`. Using deprecated traits in PHPDoc tags maintains a dependency on code that is scheduled for removal and should be migrated to the replacement.

## How to fix it

Update the `@var` tag to reference the non-deprecated replacement type:

```diff-php
 <?php declare(strict_types = 1);

-/** @var OldHelper $helper */
+/** @var NewHelper $helper */
 $helper = getHelper();
```
