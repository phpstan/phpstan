---
title: "generics.variance"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template-covariant T
 */
interface Collection
{
	/**
	 * @param T $item
	 */
	public function add($item): void;
}
```

## Why is it reported?

The template type `T` is declared as covariant (`@template-covariant`), but it appears in a contravariant position (as a method parameter type). Covariant types can only appear in "output" positions (return types), while contravariant types can only appear in "input" positions (parameter types). Using a template type in the wrong position breaks type safety.

In this example, `T` is covariant but is used as a parameter type in `add()`, which is a contravariant position.

## How to fix it

If the type should be covariant (read-only collection), remove the method that uses it in a contravariant position:

```diff-php
 /**
  * @template-covariant T
  */
 interface Collection
 {
-	/**
-	 * @param T $item
-	 */
-	public function add($item): void;
+	/**
+	 * @return T
+	 */
+	public function first();
 }
```

If the type needs to appear in both parameter and return positions, make it invariant:

```diff-php
 /**
- * @template-covariant T
+ * @template T
  */
 interface Collection
 {
 	/**
 	 * @param T $item
 	 */
 	public function add($item): void;
 }
```
