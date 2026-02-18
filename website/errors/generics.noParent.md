---
title: "generics.noParent"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @template U of \Exception
 */
class Collection
{
}

/**
 * @extends Collection<int, \InvalidArgumentException>
 */
class FooDoesNotExtendAnything
{
}
```

## Why is it reported?

The class has an `@extends` PHPDoc tag specifying generic type arguments for `Collection`, but it does not actually extend any class. The `@extends` tag is meaningless without a corresponding `extends` clause in the class declaration.

The same applies to `@implements` tags on classes that do not implement the referenced interface, and `@use` tags on classes that do not use the referenced trait.

## How to fix it

Add the missing `extends` clause:

```diff-php
 /**
  * @extends Collection<int, \InvalidArgumentException>
  */
-class FooDoesNotExtendAnything
+class FooDoesNotExtendAnything extends Collection
 {
 }
```

Or remove the unnecessary `@extends` tag if the class is not supposed to extend `Collection`:

```diff-php
-/**
- * @extends Collection<int, \InvalidArgumentException>
- */
 class FooDoesNotExtendAnything
 {
 }
```
