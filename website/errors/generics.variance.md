---
title: "generics.variance"
shortDescription: "Template type is used in a position that violates its variance."
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

Learn more: [What's Up With @template-covariant?](/blog/whats-up-with-template-covariant)

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

Split the interface into a covariant read-only part and an invariant mutable part:

```php
/**
 * @template-covariant T
 */
interface ReadonlyCollection
{
	/**
	 * @return T
	 */
	public function first();
}

/**
 * @template T
 * @extends ReadonlyCollection<T>
 */
interface Collection extends ReadonlyCollection
{
	/**
	 * @param T $item
	 */
	public function add($item): void;
}
```

Use call-site variance instead: keep the interface invariant, and annotate the variance at the point of use with `covariant` or `contravariant` keywords in the generic type argument. This avoids restricting the interface itself while preserving type safety where it matters:

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
+
+	/**
+	 * @return T
+	 */
+	public function first();
 }
+
+/**
+ * Accepts Collection<Cat> for Collection<Animal>:
+ * @param Collection<covariant Animal> $animals
+ */
+function foo(Collection $animals): void
+{
+	$animals->first(); // OK, returns Animal
+	// $animals->add(new Dog()); // Error: expects never
+}
 ```

Learn more: [A guide to call-site generic variance](/blog/guide-to-call-site-generic-variance)
