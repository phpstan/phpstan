---
title: "generics.notCompatible"
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
 * @implements int
 */
class NumberList implements Collection
{
}
```

## Why is it reported?

The `@implements`, `@extends`, or `@use` PHPDoc tag specifies a type that is not a valid generic type for the referenced class or interface. The type in the tag must be a generic type like `Collection<int>`, not a plain type like `int`. PHPStan expects the tag to match the generic signature of the parent class or interface.

## How to fix it

Use the correct generic type syntax in the PHPDoc tag:

```diff-php
 /**
- * @implements int
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
