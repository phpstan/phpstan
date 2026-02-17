---
title: "interface.extendsDeprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

interface MyInterface extends OldHelper // ERROR: Interface MyInterface extends deprecated trait OldHelper.
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

An interface attempts to extend a trait that has been marked as deprecated with the `@deprecated` PHPDoc tag. While this is already invalid PHP (interfaces cannot extend traits), the deprecation rule also flags that the referenced trait is deprecated and should no longer be used.

## How to fix it

Remove the deprecated trait from the `extends` clause and extend a proper interface instead:

```diff-php
 <?php declare(strict_types = 1);

-interface MyInterface extends OldHelper
+interface MyInterface extends NewHelperInterface
 {
 }
```
