---
title: "generics.wrongParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @template T */
interface Collection
{
}

/**
 * @implements \ArrayAccess<string, int>
 */
class MyCollection implements Collection
{
}
```

## Why is it reported?

A `@extends` or `@implements` PHPDoc tag references a class or interface that the current class does not actually extend or implement. The generic type annotation must match one of the actual parent classes or interfaces in the class declaration.

## How to fix it

Make sure the PHPDoc tag matches the actual parent class or interface:

```diff-php
 <?php declare(strict_types = 1);

 /** @template T */
 interface Collection
 {
 }

 /**
- * @implements \ArrayAccess<string, int>
+ * @implements Collection<int>
  */
 class MyCollection implements Collection
 {
 }
```
