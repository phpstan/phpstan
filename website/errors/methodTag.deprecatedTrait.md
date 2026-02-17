---
title: "methodTag.deprecatedTrait"
ignorable: true
---

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void {}
}

/**
 * @method OldHelper getHelper()
 */
class Service
{
}
```

## Why is it reported?

A `@method` PHPDoc tag references a deprecated trait in its type declaration. The trait has been marked with a `@deprecated` PHPDoc tag, indicating it should no longer be used. PHPDoc annotations should not reference deprecated types as they are planned for removal in a future version.

## How to fix it

Update the `@method` tag to use the replacement type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @method OldHelper getHelper()
+ * @method NewHelper getHelper()
  */
 class Service
 {
 }
```
