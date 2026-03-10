---
title: "generics.notCompatible"
shortDescription: "PHPDoc @implements or @extends tag specifies an invalid generic type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
interface Collection
{
}

/**
 * @implements class-string<int>
 */
class NumberList implements Collection
{
}
```

## Why is it reported?

The `@implements`, `@extends`, or `@use` PHPDoc tag specifies a type that is not a valid generic type for the referenced class or interface. The type in the tag must be a generic object type like `Collection<int>`, not a scalar type, `class-string`, or other non-generic type. PHPStan expects the tag to match the generic signature of the parent class or interface.

In the example above, `@implements class-string<int>` is not a generic object type -- it is a `class-string` type and cannot be used as an `@implements` value.

## How to fix it

Use the correct generic type syntax in the PHPDoc tag:

```diff-php
 /**
- * @implements class-string<int>
+ * @implements Collection<int>
  */
 class NumberList implements Collection
 {
 }
```

If the parent class or interface has multiple template parameters, specify all of them:

```diff-php
 /**
- * @implements Map<string>
+ * @implements Map<string, int>
  */
 class StringIntMap implements Map
 {
 }
```
