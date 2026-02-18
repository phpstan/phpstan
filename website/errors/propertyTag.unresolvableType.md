---
title: "propertyTag.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @property ArrayAccess&int $items
 */
class DataContainer
{
}
```

## Why is it reported?

The `@property` PHPDoc tag contains a type that cannot be resolved to a valid type. This typically happens when using an intersection type that combines incompatible types (such as an object type with a scalar type), resulting in a type that can never exist.

In the example above, `ArrayAccess&int` is an intersection of an object type with a scalar type. No value can be both an `ArrayAccess` object and an `int` at the same time, making this type impossible.

## How to fix it

Correct the type in the `@property` tag to use a valid, resolvable type:

```diff-php
 /**
- * @property ArrayAccess&int $items
+ * @property ArrayAccess $items
  */
 class DataContainer
 {
 }
```

If the intent is to express a union type, use `|` instead of `&`:

```diff-php
 /**
- * @property ArrayAccess&int $items
+ * @property ArrayAccess|int $items
  */
 class DataContainer
 {
 }
```
