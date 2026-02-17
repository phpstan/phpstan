---
title: "generics.deprecatedInterfaceDefault"
ignorable: true
---

This error is reported by `phpstan/phpstan-deprecation-rules`.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
}

/**
 * @template T = OldInterface
 */
class Container
{
}
```

## Why is it reported?

A `@template` tag uses a deprecated interface as its default type. The interface referenced in the default value of the template parameter has been marked with the `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement. When the deprecated interface is removed, the template default will become invalid.

## How to fix it

Replace the deprecated interface with its recommended replacement in the template default:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = OldInterface
+ * @template T = NewInterface
  */
 class Container
 {
 }
```
