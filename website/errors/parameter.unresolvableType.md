---
title: "parameter.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Collection
{
	/**
	 * @param T<int> $item
	 */
	public function add(mixed $item): void
	{
	}
}
```

## Why is it reported?

The PHPDoc `@param` (or `@param-out`) tag for a parameter contains a type that PHPStan cannot resolve. This typically happens when:

- A template type is used with generic parameters it does not support (e.g. `T<int>` when `T` is a plain template type)
- A type alias or reference cannot be resolved
- The type expression is malformed or references undefined types in a way that creates circular or unresolvable definitions

In the example above, `T` is a template type that does not accept generic parameters, so `T<int>` is unresolvable.

## How to fix it

Correct the PHPDoc type so that it can be fully resolved:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
  */
 class Collection
 {
 	/**
-	 * @param T<int> $item
+	 * @param T $item
 	 */
 	public function add(mixed $item): void
 	{
 	}
 }
```

If the template type needs to be constrained, use the `@template` bound syntax instead:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T
+ * @template T of int
  */
 class Collection
 {
 	/**
-	 * @param T<int> $item
+	 * @param T $item
 	 */
 	public function add(mixed $item): void
 	{
 	}
 }
```
