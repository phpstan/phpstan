---
title: "generics.interfaceConflict"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @template T */
interface Repository
{
}

/** @extends Repository<string> */
interface StringRepository extends Repository
{
}

/** @extends Repository<int> */
interface IntRepository extends Repository
{
}

/** @implements Repository<string> */
class MyClass implements StringRepository, IntRepository
{
}
```

## Why is it reported?

A class or interface specifies conflicting type arguments for the same generic interface through different inheritance paths. When a class implements or extends the same generic interface multiple times (through different parent interfaces or classes), the template type arguments must be identical.

In the example above, `MyClass` implements both `StringRepository` (which extends `Repository<string>`) and `IntRepository` (which extends `Repository<int>`). This creates a conflict because `Repository` cannot be parameterized with both `string` and `int` at the same time.

## How to fix it

Ensure all inheritance paths specify the same type arguments for the shared interface:

```diff-php
 <?php declare(strict_types = 1);

-/** @extends Repository<int> */
-interface IntRepository extends Repository
+/** @extends Repository<string> */
+interface AnotherStringRepository extends Repository
 {
 }

 /** @implements Repository<string> */
-class MyClass implements StringRepository, IntRepository
+class MyClass implements StringRepository, AnotherStringRepository
 {
 }
```

Or restructure the class hierarchy to avoid implementing the same generic interface with different type arguments.
