---
title: "generics.deprecatedInterfaceBound"
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
 * @template T of OldInterface
 */
class Repository
{
}
```

## Why is it reported?

A `@template` tag uses a deprecated interface as its bound type. The interface referenced in the `of` clause has been marked with the `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement. When the deprecated interface is removed, the template bound will become invalid.

## How to fix it

Replace the deprecated interface with its recommended replacement in the template bound:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of OldInterface
+ * @template T of NewInterface
  */
 class Repository
 {
 }
```
