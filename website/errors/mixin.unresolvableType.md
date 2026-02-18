---
title: "mixin.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @mixin T
 */
class QueryBuilder
{
}
```

## Why is it reported?

The `@mixin` PHPDoc tag contains a type that PHPStan cannot resolve. This typically happens when the type references a template type that is not declared on the current class, references an undefined class, or uses invalid type syntax.

In the example above, `T` is used in the `@mixin` tag but is not declared as a `@template` type parameter on the class, so PHPStan cannot determine what it refers to.

## How to fix it

If the intent is to use a generic type, declare it with `@template` first:

```diff-php
+/**
+ * @template T of Connection
+ * @mixin T
+ */
-/**
- * @mixin T
- */
 class QueryBuilder
 {
 }
```

Or reference a concrete class directly:

```diff-php
 /**
- * @mixin T
+ * @mixin Connection
  */
 class QueryBuilder
 {
 }
```
