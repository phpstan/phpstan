---
title: "possiblyImpure.eval"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function compute(string $expression, bool $dynamic): mixed
{
	if ($dynamic) {
		return eval('return ' . $expression . ';'); // ERROR: Possibly impure eval in pure function compute().
	}

	return $expression;
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using `eval()` inside a pure function is considered an impure operation because `eval()` can execute arbitrary code with unpredictable side effects -- it can modify global state, output content, define new functions or classes, and generally do anything that PHP code can do.

When PHPStan cannot determine with certainty whether the `eval()` call always executes (for example, because it is inside a conditional branch), it reports a "possibly impure" error rather than a definite impure one.

## How to fix it

Remove the `eval()` call from the pure function, or remove the `@phpstan-pure` annotation if the function genuinely needs to use `eval()`:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function compute(string $expression, bool $dynamic): mixed
 {
 	if ($dynamic) {
 		return eval('return ' . $expression . ';');
 	}

 	return $expression;
 }
```

Alternatively, refactor the function to avoid `eval()` entirely and keep it pure.
