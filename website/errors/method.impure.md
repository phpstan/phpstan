---
title: "method.impure"
shortDescription: "Impure method overrides a method declared as pure in a parent class or interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @phpstan-pure */
	public function calculate(): int
	{
		return 1;
	}
}

class Bar extends Foo
{
	/** @phpstan-impure */
	public function calculate(): int // ERROR: Impure method Bar::calculate() overrides pure method Foo::calculate().
	{
		return random_int(0, 100);
	}
}
```

## Why is it reported?

When a parent class or interface declares a method as pure (`@phpstan-pure`), all overriding methods must also be pure. A pure method guarantees it has no side effects and its return value depends only on its arguments. Code that calls the parent method relies on this guarantee — if a child class introduces side effects or non-deterministic behavior, it violates the contract.

This check is enabled by [bleeding edge](/blog/what-is-bleeding-edge) configuration.

## How to fix it

Remove the `@phpstan-impure` annotation if the method is actually pure:

```diff-php
 class Bar extends Foo
 {
-	/** @phpstan-impure */
+	/** @phpstan-pure */
 	public function calculate(): int
 	{
-		return random_int(0, 100);
+		return 42;
 	}
 }
```

If the method genuinely needs to be impure, reconsider the parent's purity contract. Remove `@phpstan-pure` from the parent method if purity cannot be guaranteed across all implementations:

```diff-php
 class Foo
 {
-	/** @phpstan-pure */
 	public function calculate(): int
 	{
 		return 1;
 	}
 }
```

If the parent class uses `@phpstan-all-methods-pure`, override the specific method in the parent instead:

```diff-php
+/** @phpstan-all-methods-pure */
 class Foo
 {
+	/** @phpstan-impure */
 	public function calculate(): int
 	{
 		return 1;
 	}
 }
```
