---
title: "methodTag.deprecatedEnum"
ignorable: true
---

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

/**
 * @method OldStatus getStatus()
 */
class Order
{
}
```

## Why is it reported?

A `@method` PHPDoc tag references a deprecated enum in its type declaration. The enum has been marked with a `@deprecated` PHPDoc tag, indicating it should no longer be used. PHPDoc annotations should not reference deprecated types as they are planned for removal in a future version.

## How to fix it

Update the `@method` tag to use the replacement type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @method OldStatus getStatus()
+ * @method NewStatus getStatus()
  */
 class Order
 {
 }
```
