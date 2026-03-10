---
title: "impureMethod.pure"
shortDescription: "Method marked as @phpstan-impure has no actual side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Calculator
{
	/** @phpstan-impure */
	public function add(int $a, int $b): int
	{
		return $a + $b;
	}
}
```

## Why is it reported?

A method is marked as `@phpstan-impure` but PHPStan's analysis found no actual side effects in its body. The method does not perform I/O, modify external state, or call other impure code. Marking a side-effect-free method as impure is misleading -- callers cannot benefit from purity optimizations, and the annotation does not match the actual behavior.

This is only reported for methods that cannot be overridden -- either in a `final` class, or when the method itself is `final`. For non-final methods, a subclass might introduce side effects, so PHPStan does not report this.

## How to fix it

Remove the `@phpstan-impure` annotation since the method has no side effects:

```diff-php
-/** @phpstan-impure */
+/** @phpstan-pure */
 public function add(int $a, int $b): int
 {
 	return $a + $b;
 }
```

Or simply remove the annotation entirely and let PHPStan infer purity:

```diff-php
-/** @phpstan-impure */
 public function add(int $a, int $b): int
 {
 	return $a + $b;
 }
```
