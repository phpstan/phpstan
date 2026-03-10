---
title: "typeAlias.unresolvableType"
shortDescription: "Type alias contains a type that cannot be fully resolved."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type MyType = string&int
 */
class Foo
{
}
```

## Why is it reported?

A type alias defined via `@phpstan-type` contains a type that PHPStan cannot resolve. This happens when the type evaluates to an impossible type, such as an intersection of incompatible scalar types, or uses invalid type syntax.

In the example above, `string&int` is an impossible intersection type -- no value can be both a `string` and an `int` at the same time. PHPStan cannot resolve this to a meaningful type.

## How to fix it

Replace the unresolvable type with a valid type:

```diff-php
 /**
- * @phpstan-type MyType = string&int
+ * @phpstan-type MyType = string|int
  */
 class Foo
 {
 }
```

If the intent is to use an intersection type, it must involve compatible types (typically interfaces or classes with an inheritance relationship):

```diff-php
 /**
- * @phpstan-type MyType = string&int
+ * @phpstan-type MyType = Countable&Traversable
  */
 class Foo
 {
 }
```
