---
title: "impureFunction.pure"
shortDescription: "Function marked as @phpstan-impure has no actual side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-impure */
function add(int $a, int $b): int
{
	return $a + $b;
}
```

## Why is it reported?

A function is marked as impure (via the `@phpstan-impure` PHPDoc tag) but does not contain any actual side effects. PHPStan detects that the function has no impure points such as I/O operations, property assignments, global state access, or calls to other impure functions. If a function truly has no side effects, it should not be marked as impure.

## How to fix it

If the function genuinely has no side effects, remove the `@phpstan-impure` annotation. Optionally mark it as `@phpstan-pure` instead:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-impure */
+/** @phpstan-pure */
 function add(int $a, int $b): int
 {
 	return $a + $b;
 }
```

Or simply remove the annotation entirely and let PHPStan infer purity:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-impure */
 function add(int $a, int $b): int
 {
 	return $a + $b;
 }
```
