---
title: "typeAlias.circular"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type RecursiveAlias RecursiveAlias[]
 */
class Foo
{
}
```

## Why is it reported?

The type alias references itself, creating a circular definition that cannot be resolved. PHPStan needs to be able to fully resolve all type aliases to perform type analysis. A type alias that directly or indirectly refers back to itself creates an infinite loop in resolution.

This also applies to mutually recursive aliases:

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-type AliasA AliasB
 * @phpstan-type AliasB AliasA
 */
class Bar
{
}
```

## How to fix it

Break the circular reference by using a concrete type instead of self-referencing:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @phpstan-type RecursiveAlias RecursiveAlias[]
+ * @phpstan-type RecursiveAlias array<int, mixed>
  */
 class Foo
 {
 }
```

For tree-like structures, use a class or interface type instead of a type alias:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-type TreeNode array{value: int, children: TreeNode[]}
- */
-class Foo
+/**
+ * @phpstan-type TreeNode array{value: int, children: list<TreeNodeClass>}
+ */
+class Foo
 {
 }
```
