---
title: "generics.deprecatedClassBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewBaseClass instead */
class OldBaseClass
{
}

/**
 * @template T of OldBaseClass
 */
class Repository
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

A `@template` tag uses a deprecated class as its bound type. The class referenced in the `of` clause has been marked with the `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement. When the deprecated class is removed, the template bound will become invalid.

## How to fix it

Replace the deprecated class with its suggested replacement in the template bound:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of OldBaseClass
+ * @template T of NewBaseClass
  */
 class Repository
 {
 }
```
