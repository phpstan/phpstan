---
title: "possiblyImpure.global"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function getValue(bool $useGlobal): int
{
	if ($useGlobal) {
		global $counter;
		return $counter;
	}

	return 42;
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` may use the `global` keyword. The `global` keyword accesses mutable global state, which is a side effect. Pure functions must not have side effects and must always return the same result for the same inputs.

When PHPStan cannot determine with certainty whether the `global` statement always executes (for example, because it is inside a conditional branch), it reports a "possibly impure" error rather than a definite impure one.

## How to fix it

Pass the dependency as a parameter instead of accessing it via `global`:

```diff-php
 /**
  * @phpstan-pure
  */
-function getValue(bool $useGlobal): int
+function getValue(bool $useGlobal, int $counter = 42): int
 {
-	if ($useGlobal) {
-		global $counter;
-		return $counter;
-	}
-
-	return 42;
+	return $useGlobal ? $counter : 42;
 }
```

Or remove the `@phpstan-pure` annotation if the function genuinely needs to access global state:

```diff-php
-/**
- * @phpstan-pure
- */
 function getValue(bool $useGlobal): int
 {
 	if ($useGlobal) {
 		global $counter;
 		return $counter;
 	}

 	return 42;
 }
```
