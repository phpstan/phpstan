---
title: "methodTag.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @method int getCount(int&string $filter)
 */
class Repository
{
}
```

## Why is it reported?

A `@method` PHPDoc tag on a class contains a type that PHPStan cannot resolve. This typically happens when the tag includes an impossible intersection type (like `int&string`), a reference to a non-existent type, or another malformed type expression.

In the example above, the parameter type `int&string` is an intersection of two scalar types that can never be satisfied simultaneously, making the type unresolvable.

## How to fix it

Replace the unresolvable type with a valid type in the `@method` tag:

```diff-php
 /**
- * @method int getCount(int&string $filter)
+ * @method int getCount(int|string $filter)
  */
 class Repository
 {
 }
```

If the intersection type was intended to narrow an object type, make sure both sides are compatible object types or interfaces:

```diff-php
 /**
- * @method int getCount(int&string $filter)
+ * @method int getCount(Countable&Traversable $filter)
  */
 class Repository
 {
 }
```
