---
title: "impure.eval"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function computeValue(string $code): mixed
{
	return eval($code); // ERROR: Impure eval in pure function computeValue().
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using `eval()` inside a pure function is considered an impure operation because `eval()` can execute arbitrary code with unpredictable side effects -- it can modify global state, output content, define new functions or classes, and generally do anything that PHP code can do.

## How to fix it

Remove the `eval()` call from the pure function, or remove the `@phpstan-pure` annotation if the function genuinely needs to use `eval()`:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
-function computeValue(string $code): mixed
+function computeValue(string $code): mixed
 {
 	return eval($code);
 }
```

Alternatively, refactor the function to avoid `eval()` entirely:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function computeValue(string $code): mixed
+function computeValue(string $expression): int
 {
-	return eval($code);
+	return (int) $expression;
 }
```
