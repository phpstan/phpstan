---
title: "impureMethod.pure"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Formatter
{
	/** @phpstan-impure */
	public function format(string $value): string
	{
		return strtoupper($value);
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-impure` declares that it has side effects, but PHPStan's analysis found no actual side effects in its body. The method does not perform I/O, modify external state, throw exceptions, or call other impure code. Marking a side-effect-free function as impure is misleading -- callers cannot benefit from purity optimizations, and the annotation does not match the actual behavior.

## How to fix it

Remove the `@phpstan-impure` annotation since the method has no side effects:

```diff-php
-/** @phpstan-impure */
+/** @phpstan-pure */
 public function format(string $value): string
 {
 	return strtoupper($value);
 }
```

Or simply remove the annotation entirely and let PHPStan infer purity:

```diff-php
-/** @phpstan-impure */
 public function format(string $value): string
 {
 	return strtoupper($value);
 }
```

If the method is intended to have side effects in the future or in subclasses, mark it as `@phpstan-pure` for now and change the annotation when side effects are introduced.
