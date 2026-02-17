---
title: "mixin.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @mixin int
 */
class Foo
{

}
```

## Why is it reported?

The `@mixin` PHPDoc tag contains a non-object type. The `@mixin` tag is used to declare that a class delegates method calls and property accesses to another object. This only makes sense with object types, because scalar types like `int`, `string`, `bool`, or `array` do not have methods or properties to delegate to.

## How to fix it

Replace the non-object type with an object type in the `@mixin` tag:

```diff-php
 /**
- * @mixin int
+ * @mixin SomeClass
  */
 class Foo
 {

 }
```

If you do not need mixin behavior, remove the `@mixin` tag entirely:

```diff-php
-/**
- * @mixin int
- */
 class Foo
 {

 }
```
