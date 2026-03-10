---
title: "possiblyImpure.static"
shortDescription: "Pure function uses a static variable which persists mutable state."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function getNextId(): int
{
	static $counter = 0;

	return $counter;
}
```

## Why is it reported?

This identifier is not reported in practice because `static` variable usage is always considered definitely impure, not just possibly impure. PHPStan reports [`impure.static`](/error-identifiers/impure.static) instead.

The function or method is marked as `@phpstan-pure` but uses a `static` variable. Static variables persist their value between function calls, which means the function relies on and can modify mutable state. Pure functions must always return the same result for the same inputs and must not depend on hidden state.

## How to fix it

Replace the static variable with a parameter or a different design, or remove the `@phpstan-pure` annotation if the function intentionally depends on static state:

```diff-php
-/**
- * @phpstan-pure
- */
-function getNextId(): int
+function getNextId(int $counter): int
 {
-	static $counter = 0;
-
 	return $counter;
 }
```
