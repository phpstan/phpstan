---
title: "requireExtends.deprecatedClass"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewBase instead */
class OldBase
{

}

/**
 * @phpstan-require-extends OldBase
 */
interface HasOldBase
{

}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag references a class that has been marked as deprecated. Using a deprecated class in a require-extends constraint means that any class implementing this interface or using this trait would be required to extend a deprecated class, which should be avoided.

## How to fix it

Update the `@phpstan-require-extends` tag to reference the non-deprecated replacement class:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-extends OldBase
+ * @phpstan-require-extends NewBase
  */
 interface HasOldBase
 {

 }
```
