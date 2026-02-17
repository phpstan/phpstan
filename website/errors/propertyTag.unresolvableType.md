---
title: "propertyTag.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @property Foo&Bar&int $value
 */
class Container
{

}
```

## Why is it reported?

The `@property` PHPDoc tag contains a type that cannot be resolved to a valid type. This typically happens when using an intersection type that combines incompatible types (such as an object type with a scalar type), resulting in a type that can never exist. PHPStan cannot work with such types because they describe values that are impossible.

## How to fix it

Correct the type in the `@property` tag to use a valid, resolvable type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @property Foo&Bar&int $value
+ * @property Foo&Bar $value
  */
 class Container
 {

 }
```
