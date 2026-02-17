---
title: "generics.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

/**
 * @template T
 */
interface Collection
{
}

/**
 * @implements Collection<MyTrait>
 */
class MyCollection implements Collection
{
}
```

## Why is it reported?

A trait is being used as a type argument in a generic type. Traits cannot be used as types in PHP -- they cannot be instantiated, used in type declarations, or referenced with `instanceof`. Using a trait as a generic type argument is invalid because PHPStan cannot reason about it as a proper type.

In the example above, `MyTrait` is a trait and is passed as a type argument to `Collection<MyTrait>`. This is not a valid type.

## How to fix it

Replace the trait with a class or interface that represents the intended type:

```diff-php
 <?php declare(strict_types = 1);

-trait MyTrait
+interface MyInterface
 {
 }

 /**
- * @implements Collection<MyTrait>
+ * @implements Collection<MyInterface>
  */
 class MyCollection implements Collection
 {
 }
```

Or create an interface that the trait's users implement and use that as the type argument:

```diff-php
 <?php declare(strict_types = 1);

+interface HasMyTrait
+{
+}
+
 trait MyTrait
 {
 }

 /**
- * @implements Collection<MyTrait>
+ * @implements Collection<HasMyTrait>
  */
-class MyCollection implements Collection
+class MyCollection implements Collection, HasMyTrait
 {
+	use MyTrait;
 }
```
