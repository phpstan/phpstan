---
title: "possiblyImpure.die"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function handleError(string $message): string
{
	if ($message !== '') {
		die($message); // ERROR: Possibly impure die in pure function handleError().
	}

	return 'ok';
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using `die` (or `exit`) inside a pure function is considered an impure operation because it immediately terminates the script, which is the most extreme side effect possible -- the function never returns a value at all.

When PHPStan cannot determine with certainty whether the `die` statement always executes (for example, because it is inside a conditional branch), it reports a "possibly impure" error rather than a definite impure one.

## How to fix it

Remove the `die` call from the pure function, or remove the `@phpstan-pure` annotation if the function genuinely needs to terminate the script:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function handleError(string $message): string
 {
 	if ($message !== '') {
 		die($message);
 	}

 	return 'ok';
 }
```

Alternatively, restructure the function so that it remains pure and the caller handles the termination:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function handleError(string $message): string
+function handleError(string $message): string
 {
-	if ($message !== '') {
-		die($message);
-	}
-
-	return 'ok';
+	return $message !== '' ? $message : 'ok';
 }
```
