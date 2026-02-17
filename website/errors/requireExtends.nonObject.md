---
title: "requireExtends.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends int
 */
trait MyTrait
{
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag contains a non-object type. This tag is meant to restrict which classes can use the trait by requiring that they extend a specific class. It only makes sense with object (class) types, not scalar types like `int`, `string`, etc.

## How to fix it

Use an object (class) type in the `@phpstan-require-extends` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-require-extends int
+ * @phpstan-require-extends SomeBaseClass
  */
 trait MyTrait
 {
 }
```
