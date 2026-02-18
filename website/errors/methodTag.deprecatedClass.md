---
title: "methodTag.deprecatedClass"
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
 * @method OldService createService()
 */
class Factory
{
	// error: PHPDoc tag @method for createService() references
	//        deprecated class OldService.
}
```

## Why is it reported?

The `@method` PHPDoc tag declares a magic method whose signature references a class that has been marked as `@deprecated`. Using deprecated classes in new APIs propagates reliance on code that is scheduled for removal, making future migration harder.

## How to fix it

Update the `@method` tag to reference the replacement class instead of the deprecated one.

```diff-php
 /**
- * @method OldService createService()
+ * @method NewService createService()
  */
 class Factory
 {
 }
```
