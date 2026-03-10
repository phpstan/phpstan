---
title: "mixin.unresolvableType"
shortDescription: "PHPDoc @mixin tag contains a type that cannot be resolved."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @mixin int&string
 */
class QueryBuilder
{
}
```

## Why is it reported?

The `@mixin` PHPDoc tag contains a type that PHPStan cannot resolve. This typically happens when the type expression evaluates to an impossible type, uses invalid type syntax, or references types in a way that produces an error during type resolution.

In the example above, `int&string` is an impossible intersection type -- no value can be both `int` and `string` at the same time -- so PHPStan cannot resolve it to a meaningful type.

## How to fix it

Reference a concrete class directly:

```diff-php
 /**
- * @mixin int&string
+ * @mixin Connection
  */
 class QueryBuilder
 {
 }
```

If the intent is to use a generic type, declare it with `@template` first:

```diff-php
+/**
+ * @template T of object
+ * @mixin T
+ */
-/**
- * @mixin int&string
- */
 class QueryBuilder
 {
 }
```
