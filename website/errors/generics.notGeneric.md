---
title: "generics.notGeneric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Foo
{
}

/**
 * @implements Foo<int>
 */
class Bar implements Foo // error: Foo is not generic
{
}
```

## Why is it reported?

A PHPDoc tag specifies generic type arguments for a class or interface that does not declare any `@template` tags. Since the referenced type is not generic, providing type parameters for it is meaningless and likely indicates a mistake.

This can happen in `@extends`, `@implements`, `@use`, `@mixin`, `@param`, `@return`, `@var`, `@template` bounds, and other PHPDoc tags.

## How to fix it

If the class or interface should be generic, add the `@template` tag to its definition:

```diff-php
 <?php declare(strict_types = 1);

+/**
+ * @template T
+ */
 interface Foo
 {
 }

 /**
  * @implements Foo<int>
  */
 class Bar implements Foo
 {
 }
```

If it is not meant to be generic, remove the type arguments from the PHPDoc tag:

```diff-php
 <?php declare(strict_types = 1);

 interface Foo
 {
 }

-/**
- * @implements Foo<int>
- */
 class Bar implements Foo
 {
 }
```
