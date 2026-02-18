---
title: "mixin.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void {}
}

/**
 * @mixin OldHelper
 */
class Service
{
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `@mixin` PHPDoc tag references a trait that has been marked as `@deprecated`. Deprecated types should no longer be used as they may be removed in a future version.

## How to fix it

Replace the deprecated trait with the recommended replacement in the `@mixin` tag:

```diff-php
 /**
- * @mixin OldHelper
+ * @mixin NewHelper
  */
 class Service
 {
 }
```
